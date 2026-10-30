<?php

namespace App\Console\Commands;

use App\Jobs\PollScrapingJob;
use App\Models\ProspectingJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ReconcileScraperJobs extends Command
{
    protected $signature = 'scraper:reconcile';
    protected $description = 'Reagenda jobs do scraper que ficaram sem polling';

    public function handle(): int
    {
        $lock = Cache::lock('scraper:reconcile', 120);
        if (!$lock->get()) return self::SUCCESS;
        try {
            $active = ProspectingJob::query()->whereIn('status', ['pending', 'working', 'unknown', 'cancel_requested'])->where('scraper_slot_acquired', true)->count();
            \DB::table('scraper_capacity')->where('id', 1)->update(['used' => $active, 'updated_at' => now()]);
            ProspectingJob::query()->whereNotNull('scraper_job_id')->whereIn('status', ['pending', 'working', 'unknown', 'cancel_requested'])->where('scraper_slot_acquired', true)->where(function ($query) { $query->whereNull('polling_claimed_at')->orWhere('polling_claimed_at', '<', now()->subMinutes(10)); })->each(fn ($job) => PollScrapingJob::dispatch($job->id));
            $this->info('Jobs reconciliados.');
            return self::SUCCESS;
        } finally { $lock->release(); }
    }
}
