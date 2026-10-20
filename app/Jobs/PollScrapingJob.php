<?php
namespace App\Jobs;
use App\Models\ProspectingJob;
use App\Services\ScraperClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class PollScrapingJob implements ShouldQueue
{
 use Dispatchable, Queueable;
 public $tries=3; public $timeout=75; public $backoff=[30,120,300];
 public function __construct(public int $jobId, public ?string $claimToken=null) {}
 public function handle(): void {
  $lock=Cache::lock('scraper:poll:'.$this->jobId,120);
  if(!$lock->get()) return;
  try {
   $job=ProspectingJob::find($this->jobId);
   if(!$job||!$job->scraper_job_id||in_array($job->status,['ok','failed'],true)) return;
   $claimToken=(string) \Illuminate\Support\Str::uuid(); $this->claimToken=$claimToken;
   $claimed=ProspectingJob::whereKey($job->id)->where(function($q){$q->whereNull('polling_claimed_at')->orWhere('polling_claimed_at','<',now()->subMinutes(10));})->update(['polling_claimed_at'=>now(),'polling_claim_token'=>$claimToken]);
   if($claimed!==1) return;
   if($job->started_at && $job->started_at->diffInSeconds(now())>config('kaizen.scraper.max_duration')) { $job->update(['status'=>'unknown','error_message'=>'Tempo máximo excedido; reconciliação administrativa necessária.']); return; }
   $remote=app(ScraperClient::class)->status($job);
   $status=strtolower((string)($remote['Status']??$remote['status']??'working'));
   $progress=(int)($remote['Progress']??$remote['progress']??$job->progress);
   if(!ProspectingJob::whereKey($job->id)->where('polling_claim_token',$claimToken)->exists()) return;
   $updated=ProspectingJob::whereKey($job->id)->where('polling_claim_token',$claimToken)->update(['progress'=>min(100,max(0,$progress))]);
   if($updated!==1) return;
   if(in_array($status,['ok','done','completed'],true)){ $renew=function() use($job,$claimToken){ return ProspectingJob::whereKey($job->id)->where('polling_claim_token',$claimToken)->update(['polling_claimed_at'=>now()])===1; }; app(ScraperClient::class)->import($job,$claimToken,$renew); $this->finish($job,'ok',null,$claimToken); return; }
   if(in_array($status,['failed','error'],true)){ $this->finish($job,'failed','Scraper reportou falha.',$claimToken); return; }
   self::dispatch($job->id)->delay(now()->addSeconds(config('kaizen.scraper.poll_seconds')));
  } catch(\Throwable $e) {
   Log::warning('Scraper polling failed',['job_id'=>$this->jobId,'error'=>$e->getMessage()]);
   throw $e;
  } finally {
   if(isset($claimToken)) ProspectingJob::whereKey($this->jobId)->where('polling_claim_token',$claimToken)->update(['polling_claimed_at'=>null,'polling_claim_token'=>null]);
   $lock->release();
  }
 }
 private function finish(ProspectingJob $job,string $status,?string $error,?string $claimToken=null): void {
  DB::transaction(function() use($job,$status,$error,$claimToken){
   $locked=ProspectingJob::whereKey($job->id)->when($claimToken,fn($q)=>$q->where('polling_claim_token',$claimToken))->lockForUpdate()->first();
   if(!$locked) return;
   $capacity=DB::table('scraper_capacity')->where('id',1)->lockForUpdate()->first();
   if($locked->scraper_slot_acquired && $capacity && $capacity->used>0) DB::table('scraper_capacity')->where('id',1)->update(['used'=>$capacity->used-1,'updated_at'=>now()]);
   $locked->update(['status'=>$status,'progress'=>$status==='ok'?100:$locked->progress,'scraper_slot_acquired'=>false,'error_message'=>$error,'finished_at'=>now(),'polling_claimed_at'=>null,'polling_claim_token'=>null]);
  });
 }
public function failed(\Throwable $e): void { $q=ProspectingJob::whereKey($this->jobId)->whereNotIn('status',['ok','failed']); if($this->claimToken) $q->where('polling_claim_token',$this->claimToken); else $q->whereNull('polling_claim_token'); $job=$q->first(); if($job) $job->update(['status'=>'unknown','error_message'=>'Polling failure; manual reconciliation required.']); Log::warning('Polling failed; capacity preserved',['job_id'=>$this->jobId]); }
}
