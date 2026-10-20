<?php
// Worker auxiliar para cen?rios de concorr?ncia real. S? aceita APP_ENV=testing e banco MYSQL_TEST_DATABASE.
if (getenv('APP_ENV') !== 'testing' || getenv('DB_DATABASE') !== getenv('MYSQL_TEST_DATABASE')) exit(64);
require dirname(__DIR__,2).'/vendor/autoload.php';
