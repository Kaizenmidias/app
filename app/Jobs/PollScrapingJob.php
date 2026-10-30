<?php

namespace App\Jobs;

use App\Models\{ProspectingJob, ProspectingJobAttempt};
use App\Services\ScraperClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PollScrapingJob implements ShouldQueue
{
    use Dispatchable, Queueable;
    public $tries = 3;
    public $timeout = 75;
    public $backoff = [30, 120, 300];

    public function __construct(public int $jobId, public ?string $claimToken = null) {}

    public function handle(): void
    {
        $lock = Cache::lock('scraper:poll:'.$this->jobId, 120);
        if (!$lock->get()) return;
        $claimToken = null;
        try {
            $job = ProspectingJob::query()->find($this->jobId);
            if (!$job || !$job->scraper_job_id || in_array($job->status, ['ok', 'failed'], true)) return;
            $claimToken = (string) Str::uuid();
            $this->claimToken = $claimToken;
            $claimed = ProspectingJob::query()->whereKey($job->id)->where(function ($query) {
                $query->whereNull('polling_claimed_at')->orWhere('polling_claimed_at', '<', now()->subMinutes(10));
            })->update(['polling_claimed_at' => now(), 'polling_claim_token' => $claimToken]);
            if ($claimed !== 1) return;
            if ($job->status === 'cancel_requested') {
                $cancelled = app(ScraperClient::class)->cancel($job);
                if ($cancelled) { $this->finish($job, 'cancelled', 'Busca cancelada pelo usuario.', $claimToken); return; }
            }
            $remote = app(ScraperClient::class)->status($job);
            $status = strtolower((string) ($remote['Status'] ?? $remote['status'] ?? 'working'));
            $attempt = ProspectingJobAttempt::query()->where('prospecting_job_id', $job->id)->where('external_id', $job->scraper_job_id)->latest()->first();
            if ($attempt) $attempt->update(['status' => in_array($status, ['ok', 'done', 'completed'], true) ? 'succeeded' : (in_array($status, ['failed', 'error'], true) ? 'failed' : 'running'), 'finished_at' => in_array($status, ['ok', 'done', 'completed', 'failed', 'error'], true) ? now() : null]);
            $progress = (int) ($remote['Progress'] ?? $remote['progress'] ?? $job->progress);
            if (ProspectingJob::query()->whereKey($job->id)->where('polling_claim_token', $claimToken)->update(['progress' => min(100, max(0, $progress)), 'updated_at' => now()->addSecond()]) !== 1) return;
            if (in_array($status, ['ok', 'done', 'completed'], true)) {
                ProspectingJob::query()->whereKey($job->id)->where('polling_claim_token', $claimToken)->update(['status' => 'importing', 'updated_at' => now()]);
                $renew = function () use ($job, $claimToken): bool {
                    $owned = ProspectingJob::query()->whereKey($job->id)->where('polling_claim_token', $claimToken)->exists();
                    if ($owned) ProspectingJob::query()->whereKey($job->id)->where('polling_claim_token', $claimToken)->update(['polling_claimed_at' => now()->addSecond()]);
                    return $owned;
                };
                $outcome = app(ScraperClient::class)->import($job, $claimToken, $renew);
                if (!$outcome->complete || $outcome->ownershipLost) {
                    if ($attempt) $attempt->update(['status' => 'unknown', 'error_message' => 'Importacao incompleta; reconciliacao necessaria.']);
                    ProspectingJob::query()->whereKey($job->id)->where('polling_claim_token', $claimToken)->update(['status' => 'unknown', 'error_message' => 'Importação incompleta; reconciliação necessária.']);
                    return;
                }
                $parameters = $job->search_parameters ?? [];
                $requested = (int) ($parameters['max_results'] ?? match ((int) ($parameters['depth'] ?? 5)) { 1 => 20, 4 => 50, 8 => 100, default => 50 });
                $previous = $parameters['import_report'] ?? [];
                $added = (int) ($previous['added'] ?? 0) + $outcome->accepted;
                $duplicates = (int) ($previous['duplicates'] ?? 0) + $outcome->updated;
                $attempts = (int) ($parameters['refill_attempts'] ?? 0);
                $remaining = max(0, $requested - $added);
                $noProgress = $outcome->accepted === 0;
                $candidateLimit = max($requested, $requested * 3);
                $candidateTotal = $added + $duplicates;
                if (str_starts_with((string) $job->scraper_job_id, 'apify:') && $remaining > 0 && !$noProgress && $candidateTotal < $candidateLimit && $attempts < 3) {
                    ProspectingJob::query()->whereKey($job->id)->where('polling_claim_token', $claimToken)->update(['status' => 'refilling', 'updated_at' => now()]);
                    $parameters['refill_attempts'] = $attempts + 1;
                    $parameters['refill_max_results'] = min($requested, $remaining);
                    $parameters['import_report'] = ['requested' => $requested, 'collected' => $added + $duplicates, 'added' => $added, 'duplicates' => $duplicates, 'rejected' => (int) ($previous['rejected'] ?? 0) + $outcome->rejected, 'total' => $added, 'completed_at' => null];
                    $nextJob = $job->fresh(['user']);
                    $nextJob->search_parameters = $parameters;
                    $refillAttempt = ProspectingJobAttempt::create(['prospecting_job_id' => $job->id, 'provider' => 'apify', 'status' => 'submitting', 'request_hash' => hash('sha256', json_encode([$nextJob->keywords, $nextJob->location, $parameters], JSON_UNESCAPED_UNICODE)), 'started_at' => now()]);
                    $nextId = app(ScraperClient::class)->create($nextJob);
                    $refillAttempt->update(['external_id' => $nextId, 'status' => 'submitted']);
                    ProspectingJob::query()->whereKey($job->id)->where('polling_claim_token', $claimToken)->update(['scraper_job_id' => $nextId, 'status' => 'working', 'progress' => 1, 'search_parameters' => $parameters]);
                    self::dispatch($job->id)->delay(now()->addSeconds(config('kaizen.scraper.poll_seconds')));
                    return;
                }
                unset($parameters['refill_max_results'], $parameters['refill_attempts']);
                $parameters['import_report'] = ['requested' => $requested, 'collected' => $added + $duplicates, 'added' => $added, 'duplicates' => $duplicates, 'rejected' => (int) ($previous['rejected'] ?? 0) + $outcome->rejected, 'total' => $added, 'partial_reason' => $remaining > 0 ? ($noProgress ? 'no_new_leads' : 'provider_limit') : null, 'completed_at' => now()->toIso8601String()];
                ProspectingJob::query()->whereKey($job->id)->where('polling_claim_token', $claimToken)->update(['search_parameters' => $parameters]);
                $this->finish($job, $remaining > 0 ? 'partial' : 'ok', $remaining > 0 ? 'O provedor nao retornou novos leads suficientes.' : null, $claimToken);
                return;
            }
            if (in_array($status, ['failed', 'error'], true)) { $this->finish($job, 'failed', 'Scraper reportou falha.', $claimToken); return; }
            if ($job->started_at && $job->started_at->diffInSeconds(now()) > config('kaizen.scraper.max_duration')) { ProspectingJob::query()->whereKey($job->id)->where('polling_claim_token', $claimToken)->update(['status' => 'unknown', 'error_message' => 'Tempo máximo excedido; reconciliação administrativa necessária.']); return; }
            self::dispatch($job->id)->delay(now()->addSeconds(config('kaizen.scraper.poll_seconds')));
        } catch (\Throwable $e) {
            Log::warning('Scraper polling failed', ['job_id' => $this->jobId, 'error' => $e->getMessage()]);
            throw $e;
        } finally {
            if ($claimToken) ProspectingJob::query()->whereKey($this->jobId)->where('polling_claim_token', $claimToken)->update(['polling_claimed_at' => null, 'polling_claim_token' => null]);
            $lock->release();
        }
    }

    private function finish(ProspectingJob $job, string $status, ?string $error, string $claimToken): void
    {
        DB::transaction(function () use ($job, $status, $error, $claimToken) {
            $locked = ProspectingJob::query()->whereKey($job->id)->where('polling_claim_token', $claimToken)->lockForUpdate()->first();
            if (!$locked) return;
            $capacity = DB::table('scraper_capacity')->where('id', 1)->lockForUpdate()->first();
            if ($locked->scraper_slot_acquired && $capacity && $capacity->used > 0) DB::table('scraper_capacity')->where('id', 1)->update(['used' => $capacity->used - 1, 'updated_at' => now()]);
            $locked->update(['status' => $status, 'progress' => in_array($status, ['ok', 'partial'], true) ? 100 : $locked->progress, 'scraper_slot_acquired' => false, 'error_message' => $error, 'finished_at' => now(), 'polling_claimed_at' => null, 'polling_claim_token' => null]);
        }, 3);
    }

    public function failed(\Throwable $e): void
    {
        DB::transaction(function () {
            $query = ProspectingJob::query()->whereKey($this->jobId)->whereNotIn('status', ['ok', 'failed'])->lockForUpdate();
            if ($this->claimToken) $query->where('polling_claim_token', $this->claimToken); else $query->whereNull('polling_claim_token');
            $job = $query->first();
            if ($job) $job->update(['status' => 'unknown', 'error_message' => 'Falha ao consultar o scraper; reconciliação necessária.']);
        });
        Log::warning('Polling failed; capacity preserved', ['job_id' => $this->jobId]);
    }
}
