<?php

namespace App\Jobs;

use App\Models\ProspectingJob;
use App\Services\ScraperClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RunScrapingJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public $tries = 3;
    public $timeout = 75;
    public $backoff = [30, 120, 300];

    public function __construct(public int $jobId) {}

    public function handle(): void
    {
        $attemptToken = (string) Str::uuid();
        $claimed = DB::transaction(function () use ($attemptToken) {
            $current = ProspectingJob::query()->whereKey($this->jobId)->lockForUpdate()->first();
            $capacity = DB::table('scraper_capacity')->where('id', 1)->lockForUpdate()->first();
            $limit = max(1, (int) config('kaizen.apify.concurrency', 4));
            if (!$current || !$capacity || $current->scraper_job_id || in_array($current->status, ['ok', 'failed'], true) || $current->scraper_slot_acquired || $capacity->used >= $limit) return false;
            DB::table('scraper_capacity')->where('id', 1)->update(['used' => $capacity->used + 1, 'updated_at' => now()]);
            $current->update(['scraper_slot_acquired' => true, 'status' => 'working', 'started_at' => $current->started_at ?: now(), 'progress' => 1, 'submission_intent_at' => now(), 'submission_attempt_token' => $attemptToken]);
            return true;
        }, 3);
        if (!$claimed) {
            $pending = ProspectingJob::query()->find($this->jobId);
            if ($pending && $pending->status === 'pending' && !$pending->scraper_job_id && $pending->created_at?->gt(now()->subMinutes(30))) {
                self::dispatch($this->jobId)->delay(now()->addSeconds(15));
            }
            return;
        }

        $job = ProspectingJob::query()->findOrFail($this->jobId);
        try {
            $externalId = app(ScraperClient::class)->create($job);
            $saved = ProspectingJob::query()->whereKey($this->jobId)->where('submission_attempt_token', $attemptToken)->whereNull('scraper_job_id')->update(['scraper_job_id' => $externalId, 'submission_intent_at' => null, 'submission_attempt_token' => null, 'updated_at' => now()]);
            if ($saved !== 1) return;
        } catch (\Throwable $e) {
            $definitiveRejection = $e->getCode() >= 400 && $e->getCode() < 500;
            DB::transaction(function () use ($attemptToken, $e, $definitiveRejection) {
                $locked = ProspectingJob::query()->whereKey($this->jobId)->where('submission_attempt_token', $attemptToken)->lockForUpdate()->first();
                if (!$locked || $locked->scraper_job_id || in_array($locked->status, ['ok', 'failed'], true)) return;
                if ($locked->scraper_slot_acquired) {
                    $capacity = DB::table('scraper_capacity')->where('id', 1)->lockForUpdate()->first();
                    if ($capacity && $capacity->used > 0) DB::table('scraper_capacity')->where('id', 1)->update(['used' => $capacity->used - 1, 'updated_at' => now()]);
                }
                $locked->update([
                    'status' => $definitiveRejection ? 'failed' : 'unknown',
                    'error_message' => $definitiveRejection ? $e->getMessage() : 'N�o foi poss�vel confirmar a cria��o na Apify. Reconcilia��o necess�ria.',
                    'scraper_slot_acquired' => false,
                    'submission_intent_at' => null,
                    'submission_attempt_token' => null,
                    'finished_at' => $definitiveRejection ? now() : null,
                ]);
            });
            Log::warning('Scraper submission outcome unknown', ['job_id' => $this->jobId, 'error' => $e->getMessage()]);
            return;
        }
        Log::info('Scraper job submitted', ['job_id' => $this->jobId]);
        PollScrapingJob::dispatch($this->jobId)->delay(now()->addSeconds(config('kaizen.scraper.poll_seconds')));
    }

    public function failed(\Throwable $e): void
    {
        DB::transaction(function () {
            $locked = ProspectingJob::query()->whereKey($this->jobId)->lockForUpdate()->first();
            if (!$locked || $locked->scraper_job_id || in_array($locked->status, ['ok', 'failed'], true)) return;
            $locked->update(['status' => 'unknown', 'error_message' => 'Falha durante submiss�o; estado externo incerto.']);
        });
        Log::error($e->getMessage(), ['job_id' => $this->jobId]);
    }
}
