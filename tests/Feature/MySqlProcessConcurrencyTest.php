<?php
namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
class MySqlProcessConcurrencyTest extends TestCase
{
 use RefreshDatabase;
 protected function setUp(): void { parent::setUp(); if((getenv('APP_ENV') ?: env('APP_ENV'))!=='testing'||(getenv('MYSQL_TEST_ENABLED') ?: env('MYSQL_TEST_ENABLED'))!=='true'||(getenv('DB_CONNECTION') ?: env('DB_CONNECTION'))!=='mysql'||(getenv('DB_DATABASE') ?: env('DB_DATABASE'))!==(getenv('MYSQL_TEST_DATABASE') ?: env('MYSQL_TEST_DATABASE'))||in_array((getenv('DB_DATABASE') ?: env('DB_DATABASE')),['kaizen_partners','production','prod'],true)) $this->markTestSkipped('MySQL de teste n?o habilitado.'); }
 private function runPair(string $mode,array $extra=[]): array {
  $dir=sys_get_temp_dir().'/kaizen-mysql-'.bin2hex(random_bytes(4));
  mkdir($dir); $procs=[]; $logs=[];
  $base=['APP_ENV'=>'testing','MYSQL_TEST_ENABLED'=>'true','DB_CONNECTION'=>'mysql','DB_HOST'=>'127.0.0.1','DB_PORT'=>'13306','DB_DATABASE'=>'kaizen_partners_test','MYSQL_TEST_DATABASE'=>'kaizen_partners_test','DB_USERNAME'=>'kaizen_test','DB_PASSWORD'=>(getenv('DB_PASSWORD') ?: config('database.connections.mysql.password'))];
  for($i=0;$i<2;$i++){
   $env=array_merge($base,$extra,['WORKER_ID'=>(string)$i]); $logs[$i]=$dir.'/worker-'.$i;
   $cmd=PHP_BINARY.' '.escapeshellarg(base_path('tests/Support/mysql-concurrency-worker.php')).' '.escapeshellarg($mode);
   $descriptor=[1=>['file',$logs[$i].'.out','w'],2=>['file',$logs[$i].'.err','w']]; $pipes=[];
   $procs[$i]=proc_open($cmd,$descriptor,$pipes,base_path(),$env);
  }
  $out=[];
  foreach($procs as $i=>$proc){
   $deadline=microtime(true)+10;
   while(($status=proc_get_status($proc))['running'] && microtime(true)<$deadline) usleep(10000);
   if($status['running']) { proc_terminate($proc); proc_close($proc); throw new \RuntimeException('worker timeout: '.trim((string)@file_get_contents($logs[$i].'.err'))); }
   $exit=proc_close($proc); $out[]=trim((string)@file_get_contents($logs[$i].'.out')); if($exit!==0) throw new \RuntimeException('worker exit '.$exit.': '.trim((string)@file_get_contents($logs[$i].'.err')));
  }
  @rmdir($dir); return $out;
 }
 public function test_two_processes_only_one_capacity_winner(): void { DB::table('scraper_capacity')->update(['used'=>0,'capacity'=>1]); $r=$this->runPair('capacity'); $this->assertCount(1,array_filter($r,fn($x)=>$x==='won')); }
 public function test_two_processes_only_one_claim_winner(): void { $u=\App\Models\User::factory()->create(); $j=\App\Models\ProspectingJob::create(['user_id'=>$u->id,'keywords'=>['x'],'status'=>'working','scraper_job_id'=>'external','scraper_slot_acquired'=>true]); $r=$this->runPair('claim',['MYSQL_TEST_JOB_ID'=>$j->id]); $this->assertCount(1,array_filter($r,fn($x)=>$x==='won')); }
 public function test_unique_external_place_conflict_is_observed(): void { $u=\App\Models\User::factory()->create(); $r=$this->runPair('unique',['MYSQL_TEST_USER_ID'=>$u->id]); $this->assertCount(1,array_filter($r,fn($x)=>$x==='inserted')); }
}






