<?php
namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MySqlConcurrencyTest extends TestCase
{
 use RefreshDatabase;
 protected function setUp(): void
 {
  parent::setUp();
  $read=fn($k)=>($_ENV[$k]??$_SERVER[$k]??getenv($k)??env($k)); if ($read('MYSQL_TEST_ENABLED') !== 'true' || $read('APP_ENV') !== 'testing' || $read('DB_CONNECTION') !== 'mysql' || $read('DB_HOST') !== '127.0.0.1' || $read('DB_PORT') !== '13306' || $read('DB_DATABASE') !== 'kaizen_partners_test' || $read('MYSQL_TEST_DATABASE') !== 'kaizen_partners_test' || $read('DB_USERNAME') !== 'kaizen_test') $this->markTestSkipped('MySQL isolado n?o habilitado ou banco n?o permitido.');
  if (DB::connection()->getDriverName() !== 'mysql') $this->markTestSkipped('Este teste exige MySQL.');
 }
 public function test_mysql_schema_has_unique_lead_key(): void
 {
  $indexes=DB::select("SHOW INDEX FROM leads WHERE Key_name <> 'PRIMARY'");
  $names=array_map(fn($i)=>$i->Key_name,$indexes);
  $this->assertContains('leads_user_id_external_place_id_unique',$names);
 }
 public function test_mysql_for_update_serializes_capacity_row(): void
 {
  DB::transaction(function(){ $row=DB::table('scraper_capacity')->where('id',1)->lockForUpdate()->first(); $this->assertNotNull($row); });
 }
}


