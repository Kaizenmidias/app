<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
class TestingMigrate extends Command
{
 protected $signature='testing:migrate'; protected $description='Executa migrations somente no MySQL isolado de testes';
 public function handle(): int
 {
  $value=fn(string $key)=>(getenv($key)!==false?getenv($key):env($key));
  $required=['APP_ENV'=>'testing','MYSQL_TEST_ENABLED'=>'true','DB_CONNECTION'=>'mysql','DB_HOST'=>'127.0.0.1','DB_PORT'=>'13306','DB_DATABASE'=>'kaizen_partners_test','MYSQL_TEST_DATABASE'=>'kaizen_partners_test','DB_USERNAME'=>'kaizen_test'];
  foreach($required as $key=>$expected) if((string)$value($key)!==$expected){$this->error("Configuração inválida: {$key}"); return self::FAILURE;}
  if(in_array($value('DB_DATABASE'),['kaizen_partners','production','prod'],true)){ $this->error('Banco de produção bloqueado.'); return self::FAILURE; }
  try { $row=DB::selectOne('SELECT DATABASE() db, CURRENT_USER() user, @@hostname host, @@port port'); } catch(\Throwable $e) { $this->error('Conexão de teste não pôde ser validada.'); return self::FAILURE; }
  if($row->db!=='kaizen_partners_test'||$row->user!=='kaizen_test@%'|| (string)$row->port!=='3306'){ $this->error('Identidade efetiva da conexão divergente.'); return self::FAILURE; }
  return Artisan::call('migrate',['--force'=>true])===0?self::SUCCESS:self::FAILURE
; }
}
