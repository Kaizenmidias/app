<?php
namespace App\Console\Commands;
use App\Models\ProspectingJob;
use Illuminate\Console\Command;
class InspectUnknownScraperJobs extends Command
{
 protected $signature = 'scraper:unknown';
 protected $description = 'Lista jobs unknown que exigem reconcilia??o administrativa';
 public function handle(): int
 {
  $jobs=ProspectingJob::where('status','unknown')->orderBy('updated_at')->get(['id','user_id','scraper_job_id','error_message','scraper_slot_acquired','started_at','updated_at']);
  if($jobs->isEmpty()){ $this->info('Nenhum job unknown.'); return self::SUCCESS; }
  $this->table(['id','user_id','scraper_job_id','error','slot','started_at','updated_at'],$jobs->map(fn($j)=>[$j->id,$j->user_id,$j->scraper_job_id?:'(none)',$j->error_message,$j->scraper_slot_acquired?'yes':'no',$j->started_at,$j->updated_at])->all());
  $this->warn('Nenhuma vaga foi liberada e nenhuma busca foi reenviada. Confirme o encerramento externo antes de qualquer a??o administrativa.');
  return self::SUCCESS;
 }
}
