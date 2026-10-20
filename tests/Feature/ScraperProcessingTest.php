<?php
namespace Tests\Feature;
use App\Jobs\PollScrapingJob;
use App\Models\{ProspectingJob,User,Lead};
use App\Services\ScraperClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
class ScraperProcessingTest extends TestCase
{
 use RefreshDatabase;
 public function test_scraper_endpoints_require_authentication_and_csrf(): void {
  $this->postJson('/api/v1/jobs', ['keywords'=>['x'],'lat'=>'1','lon'=>'2','max_time'=>10])->assertUnauthorized();
  $u=User::factory()->create(['email_verified_at'=>now()]);
  $this->actingAs($u)->postJson('/api/v1/jobs',['keywords'=>['x'],'lat'=>'1','lon'=>'2','max_time'=>10])->assertStatus(419);
 }
 private function job(array $extra=[]): ProspectingJob { $u=User::factory()->create(['email_verified_at'=>now()]); \DB::table('scraper_capacity')->update(['used'=>1]); return ProspectingJob::create(array_merge(['user_id'=>$u->id,'keywords'=>['cafe'],'latitude'=>1,'longitude'=>2,'status'=>'working','scraper_job_id'=>'remote-1','scraper_slot_acquired'=>true,'started_at'=>now()],$extra)); }
 public function test_polling_imports_completed_csv_idempotently(): void {
  $job=$this->job(); Http::fake(['*/api/v1/jobs/remote-1'=>Http::response(['Status'=>'ok','Progress'=>100]),'*/api/v1/jobs/remote-1/download'=>Http::response("title,place_id\nCafe,p1\n")]);
  (new PollScrapingJob($job->id))->handle(); (new PollScrapingJob($job->id))->handle();
  $this->assertDatabaseHas('prospecting_jobs',['id'=>$job->id,'status'=>'ok','progress'=>100]); $this->assertSame(1,Lead::count());
 }
 public function test_polling_marks_remote_failure(): void {
  $job=$this->job(); Http::fake(['*'=>Http::response(['Status'=>'failed'])]);
  (new PollScrapingJob($job->id))->handle();
  $this->assertDatabaseHas('prospecting_jobs',['id'=>$job->id,'status'=>'failed']);
 }
}
