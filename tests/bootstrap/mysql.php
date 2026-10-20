<?php
foreach ([
 'APP_ENV'=>'testing','MYSQL_TEST_ENABLED'=>'true','DB_CONNECTION'=>'mysql',
 'DB_HOST'=>'127.0.0.1','DB_PORT'=>'13306','DB_DATABASE'=>'kaizen_partners_test',
 'MYSQL_TEST_DATABASE'=>'kaizen_partners_test','DB_USERNAME'=>'kaizen_test',
] as $key=>$value) { putenv($key.'='.$value); $_ENV[$key]=$value; $_SERVER[$key]=$value; }
require dirname(__DIR__, 2).'/vendor/autoload.php';
