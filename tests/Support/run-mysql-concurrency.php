<?php
declare(strict_types=1);
require dirname(__DIR__,2).'/vendor/autoload.php';
\Dotenv\Dotenv::createImmutable(dirname(__DIR__,2))->safeLoad();

$get=function(string $k): string { $v=getenv($k); return $v!==false?(string)$v:(string)($_ENV[$k]??$_SERVER[$k]??''); };
$required=['APP_ENV'=>'testing','MYSQL_TEST_ENABLED'=>'true','DB_CONNECTION'=>'mysql','DB_HOST'=>'127.0.0.1','DB_PORT'=>'13306','DB_DATABASE'=>'kaizen_partners_test','MYSQL_TEST_DATABASE'=>'kaizen_partners_test','DB_USERNAME'=>'kaizen_test'];
foreach($required as $k=>$v){ if($get($k)!==$v) fwrite(STDERR,"guard failed: $k\n"); }
foreach($required as $k=>$v){ if($get($k)!==$v) exit(64); }
$password=$get('DB_PASSWORD'); if($password==='') exit(64);
$scenario=$argv[1]??''; if(!in_array($scenario,['capacity','claim','unique'],true)) exit(64);
$pdo=new PDO('mysql:host='.$get('DB_HOST').';port='.$get('DB_PORT').';dbname='.$get('DB_DATABASE'),$get('DB_USERNAME'),$password,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
if($scenario==='capacity') $pdo->exec('UPDATE scraper_capacity SET used=0 WHERE id=1');
if($scenario==='unique') $pdo->exec("DELETE FROM leads WHERE external_place_id='same-place'");
$extra=[]; $fixtureUser=null; $fixtureJob=null; $external='fixture-'.bin2hex(random_bytes(6));
if($scenario==='claim'||$scenario==='unique'){
 $email='fixture-'.bin2hex(random_bytes(6)).'@example.invalid'; $st=$pdo->prepare("INSERT INTO users (name,email,password,role,status,created_at,updated_at) VALUES (?,?,?,?,?,?,?)"); $now=date('Y-m-d H:i:s'); $st->execute(['Concurrency Fixture',$email,'x','partner','active',$now,$now]); $fixtureUser=(int)$pdo->lastInsertId();
 if($scenario==='claim'){ $st=$pdo->prepare("INSERT INTO prospecting_jobs (user_id,keywords,status,scraper_slot_acquired,created_at,updated_at) VALUES (?,?,?,?,?,?)"); $st->execute([$fixtureUser,'["fixture"]','working',1,$now,$now]); $fixtureJob=(int)$pdo->lastInsertId(); $extra['MYSQL_TEST_JOB_ID']=(string)$fixtureJob; }
 if($scenario==='unique'){ $extra['MYSQL_TEST_USER_ID']=(string)$fixtureUser; $extra['MYSQL_TEST_EXTERNAL_ID']=$external; }
}
$dir=sys_get_temp_dir().'/kaizen-external-'.bin2hex(random_bytes(6)); mkdir($dir,0700); $bar=$dir.'/barrier';
$envFile=$dir.'/env'; $lines=[]; foreach(array_merge($required,['DB_PASSWORD'=>$password,'WORKER_BARRIER'=>$bar],$extra) as $k=>$v) $lines[]=$k.'='.escapeshellarg($v); file_put_contents($envFile,implode("\n",$lines)."\n"); chmod($envFile,0600);
$pids=[]; $start=microtime(true); for($i=0;$i<2;$i++){ $out=$dir."/out-$i"; $err=$dir."/err-$i"; $cmd="setsid sh -c 'set -a; . ".escapeshellarg($envFile)."; export WORKER_ID=$i; exec ".escapeshellarg(PHP_BINARY).' '.escapeshellarg(__DIR__.'/mysql-concurrency-worker.php').' '.escapeshellarg($scenario).' '.escapeshellarg($bar)."' >".escapeshellarg($out)." 2>".escapeshellarg($err).' & echo $!'; $pids[$i]=(int)trim((string)shell_exec($cmd)); }
$deadline=microtime(true)+5; for($i=0;$i<2;$i++) while(!file_exists($bar.'.ready.'.$i)&&microtime(true)<$deadline) usleep(10000); if(!file_exists($bar.'.ready.0')||!file_exists($bar.'.ready.1')) exit(70); touch($bar.'.go');
$results=[]; foreach($pids as $i=>$pid){ $end=microtime(true)+15; do { $alive=trim((string)shell_exec('kill -0 '.(int)$pid.' 2>/dev/null; echo $?'))==='0'; if(!$alive) break; usleep(20000); } while(microtime(true)<$end); if($alive) { shell_exec('kill '.(int)$pid.' 2>/dev/null'); $results[$i]=['pid'=>$pid,'result'=>'timeout','exit'=>null]; } else { $o=trim((string)@file_get_contents($dir."/out-$i")); $e=trim((string)@file_get_contents($dir."/err-$i")); $results[$i]=['pid'=>$pid,'result'=>$o,'stderr'=>$e]; } }
echo json_encode(['scenario'=>$scenario,'duration'=>round(microtime(true)-$start,3),'workers'=>$results],JSON_UNESCAPED_SLASHES).PHP_EOL;
if($fixtureJob) $pdo->prepare('DELETE FROM prospecting_jobs WHERE id=?')->execute([$fixtureJob]); if($fixtureUser) $pdo->prepare('DELETE FROM users WHERE id=?')->execute([$fixtureUser]);
foreach(glob($dir.'/*')?:[] as $f) @unlink($f); @rmdir($dir);
