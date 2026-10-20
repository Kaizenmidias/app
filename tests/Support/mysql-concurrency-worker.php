<?php
// Worker PDO para concorr?ncia real. N?o inicializa Laravel nem aceita produ??o.
if (getenv('APP_ENV') !== 'testing' || getenv('MYSQL_TEST_ENABLED') !== 'true' || getenv('DB_DATABASE') !== getenv('MYSQL_TEST_DATABASE')) exit(64);
$db=getenv('DB_DATABASE'); if (in_array($db,['kaizen_partners','production','prod'],true)) exit(64);
$mode=$argv[1]??''; $barrier=$argv[2]??'';
$dsn='mysql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT').';dbname='.$db.';charset=utf8mb4';
$pdo=new PDO($dsn,getenv('DB_USERNAME'),getenv('DB_PASSWORD'),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
if ($barrier) { file_put_contents($barrier.'.ready.'.(getenv('WORKER_ID')?:'0'), getmypid()); while(!file_exists($barrier.'.go')) usleep(10000); }
usleep(100000);
if ($mode==='capacity') { $pdo->beginTransaction(); $row=$pdo->query('SELECT used, capacity FROM scraper_capacity WHERE id=1 FOR UPDATE')->fetch(PDO::FETCH_ASSOC); $won=false; if ((int)$row['used']<(int)$row['capacity']) { $pdo->exec('UPDATE scraper_capacity SET used=used+1 WHERE id=1'); $won=true; } $pdo->commit(); echo $won?'won':'lost'; exit(0); }
if ($mode==='claim') { $pdo->beginTransaction(); $st=$pdo->prepare("UPDATE prospecting_jobs SET polling_claimed_at=NOW() WHERE id=? AND (polling_claimed_at IS NULL OR polling_claimed_at < DATE_SUB(NOW(), INTERVAL 10 MINUTE))"); $st->execute([(int)getenv('MYSQL_TEST_JOB_ID')]); $pdo->commit(); echo $st->rowCount()===1?'won':'lost'; exit(0); }
if ($mode==='unique') { try { $st=$pdo->prepare('INSERT INTO leads (user_id,name,external_place_id,created_at,updated_at) VALUES (?,?,?,?,?)'); $now=date('Y-m-d H:i:s'); $st->execute([(int)getenv('MYSQL_TEST_USER_ID'),'concurrent',getenv('MYSQL_TEST_EXTERNAL_ID')?:'same-place',$now,$now]); echo 'inserted'; } catch(Throwable $e) { echo (str_contains($e->getMessage(),'Duplicate')?'conflict':'error'); } exit(0); }
exit(65);

