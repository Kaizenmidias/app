<?php
namespace App\Jobs;
use App\Models\ProspectingJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use App\Services\ScraperClient;
use Illuminate\Support\Facades\DB;
class RunScrapingJob implements ShouldQueue
{
    use Dispatchable, Queueable;
    public $tries = 3;
    public $backoff = [30, 120, 300];
    public function __construct(public int $jobId) {}
    public function handle(): void
    {
        $job=ProspectingJob::findOrFail($this->jobId);
        if ($job->scraper_job_id || in_array($job->status, ['ok','failed'], true)) return;
        $claimed = DB::transaction(function () use ($job) {
            $current = ProspectingJob::whereKey($job->id)->lockForUpdate()->first();
            $capacity = DB::table('scraper_capacity')->where('id',1)->lockForUpdate()->first();
            if (!$current || !$capacity || $current->scraper_slot_acquired || $capacity->used >= $capacity->capacity) return false;
            DB::table('scraper_capacity')->where('id',1)->update(['used'=>$capacity->used+1,'updated_at'=>now()]);
            $current->update(['scraper_slot_acquired'=>true]); return true;
        });
        if (!$claimed) { self::dispatch($this->jobId)->delay(now()->addSeconds(30)); return; }
        try {
            $job->update(['status'=>'working','started_at'=>$job->started_at ?: now(),'progress'=>1]);
            $job->update(['scraper_job_id'=>app(ScraperClient::class)->create($job)]);
        } catch (\Throwable $e) {
            // A timeout after POST may mean Gosom accepted the job. Do not resubmit blindly.
            $job->update(['status'=>'unknown','error_message'=>'Não foi possível confirmar a criação no scraper. Reconciliação manual necessária.']);
            Log::warning('Scraper submission outcome unknown', ['job_id'=>$job->id,'error'=>$e->getMessage()]);
            return;
        }
        Log::info('Scraper job submitted', ['job_id'=>$job->id]);
        PollScrapingJob::dispatch($job->id)->delay(now()->addSeconds(config('kaizen.scraper.poll_seconds')));
    }
    public function failed(\Throwable $e): void { ProspectingJob::whereKey($this->jobId)->update(['status'=>'failed','error_message'=>'Falha no processamento.','finished_at'=>now()]); Log::error($e->getMessage(),['job_id'=>$this->jobId]); }
}
