<?php

namespace App\Console\Commands;

use App\Jobs\PollScrapingJob;
use App\Models\ProspectingJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ReconcileScraperJobs extends Command
{
    protected $signature = 'scraper:reconcile';
    protected $description = 'Reagenda jobs do scraper que ficaram sem polling';

    public function handle(): int
    {
        $lock = Cache::lock('scraper:reconcile', 120);
        if (!$lock->get()) return self::SUCCESS;
        try {
            $activeStatuses = ['pending', 'working', 'importing', 'refilling', 'unknown', 'cancel_requested'];
            $active = ProspectingJob::query()->whereIn('status', $activeStatuses)->where('scraper_slot_acquired', true)->count();
            $capacity = max(1, (int) config('kaizen.apify.concurrency', 4));
            DB::table('scraper_capacity')->where('id', 1)->update(['capacity' => $capacity, 'used' => $active, 'updated_at' => now()]);
            ProspectingJob::query()->whereNotNull('scraper_job_id')->whereIn('status', $activeStatuses)->where('scraper_slot_acquired', true)->where(function ($query) { $query->whereNull('polling_claimed_at')->orWhere('polling_claimed_at', '<', now()->subMinutes(10)); })->each(fn ($job) => PollScrapingJob::dispatch($job->id));
            ProspectingJob::query()->where('status', 'unknown')->whereNull('scraper_job_id')->where('updated_at', '<', now()->subMinutes(15))->each(function ($job) {
                DB::transaction(function () use ($job) {
                    $locked = ProspectingJob::query()->whereKey($job->id)->where('status', 'unknown')->whereNull('scraper_job_id')->lockForUpdate()->first();
                    if (!$locked) return;
                    if ($locked->scraper_slot_acquired) {
                        $capacity = DB::table('scraper_capacity')->where('id', 1)->lockForUpdate()->first();
                        if ($capacity && $capacity->used > 0) DB::table('scraper_capacity')->where('id', 1)->update(['used' => $capacity->used - 1, 'updated_at' => now()]);
                    }
                    $locked->attempts()->where('status', 'unknown')->update(['status' => 'reconciled_failed', 'finished_at' => now(), 'updated_at' => now()]);
                    $locked->update(['status' => 'failed', 'scraper_slot_acquired' => false, 'error_message' => 'Não foi possível localizar uma Run externa após a janela de reconciliação.', 'finished_at' => now()]);
                });
            });
            $this->info('Jobs reconciliados.');
            return self::SUCCESS;
        } finally { $lock->release(); }
    }
}
