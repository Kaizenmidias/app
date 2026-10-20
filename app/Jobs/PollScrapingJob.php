<?php
namespace App\Jobs;
use App\Models\ProspectingJob;
use App\Services\ScraperClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
class PollScrapingJob implements ShouldQueue
{
 use Dispatchable, Queueable;
 public $tries = 3; public $backoff = [30,120,300];
 public function __construct(public int $jobId) {}
 public function handle(): void {
  $job=ProspectingJob::find($this->jobId); if(!$job||!$job->scraper_job_id||in_array($job->status,['ok','failed'],true)) return;
  if($job->started_at && $job->started_at->diffInSeconds(now()) > config('kaizen.scraper.max_duration')) { $this->finish($job,'failed','Tempo máximo excedido'); return; }
  try { $remote=app(ScraperClient::class)->status($job); $status=strtolower((string)($remote['Status']??$remote['status']??'working')); $progress=(int)($remote['Progress']??$remote['progress']??$job->progress); $job->update(['progress'=>min(100,max(0,$progress))]);
   if(in_array($status,['ok','done','completed'],true)){ app(ScraperClient::class)->import($job); $this->finish($job,'ok',null); return; }
   if(in_array($status,['failed','error'],true)){ $this->finish($job,'failed','Scraper reportou falha.'); return; }
   self::dispatch($job->id)->delay(now()->addSeconds(config('kaizen.scraper.poll_seconds')));
  } catch(\Throwable $e) { Log::warning('Scraper polling failed',['job_id'=>$job->id,'error'=>$e->getMessage()]); throw $e; }
 }
 private function finish(ProspectingJob $job,string $status,?string $error): void { DB::transaction(function() use($job,$status,$error){ $locked=ProspectingJob::whereKey($job->id)->lockForUpdate()->first(); if(!$locked||!$locked->scraper_slot_acquired)return; $capacity=DB::table('scraper_capacity')->where('id',1)->lockForUpdate()->first(); DB::table('scraper_capacity')->where('id',1)->update(['used'=>max(0,$capacity->used-1),'updated_at'=>now()]); $locked->update(['status'=>$status,'progress'=>$status==='ok'?100:$locked->progress,'scraper_slot_acquired'=>false,'error_message'=>$error,'finished_at'=>now()]); }); }
 public function failed(\Throwable $e): void { $job=ProspectingJob::find($this->jobId); if($job)$this->finish($job,'failed','Falha ao consultar o scraper.'); }
}
