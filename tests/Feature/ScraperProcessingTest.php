<?php
namespace Tests\Feature;
use App\Jobs\PollScrapingJob;
use App\Models\{ProspectingJob,User,Lead};
use App\Services\ScraperClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
class ScraperProcessingTest extends TestCase
{
 use RefreshDatabase;
 public function test_scraper_endpoints_require_authentication_and_csrf(): void {
  $this->postJson('/api/v1/jobs', ['keywords'=>['x'],'lat'=>'1','lon'=>'2','max_time'=>10])->assertUnauthorized();
  $u=User::factory()->create(['email_verified_at'=>now()]);
  $this->actingAs($u)->postJson('/api/v1/jobs',['keywords'=>['x'],'lat'=>'1','lon'=>'2','max_time'=>10])->assertStatus(419);
 }
 public function test_standalone_prospecting_page_exposes_session_csrf_token_for_real_post(): void {
  $u=User::factory()->create(['email_verified_at'=>now()]);
  $page=$this->actingAs($u)->get('/prospecting')->assertOk();
  $this->assertStringContainsString('meta name="csrf-token"', $page->getContent());
  $this->assertStringContainsString(session()->token(), $page->getContent());
 }
 public function test_valid_prospecting_submission_is_queued_and_returns_accepted(): void {
  $u=User::factory()->create(['email_verified_at'=>now(),'status'=>'active','apify_api_token'=>'test-token']); Queue::fake(); $this->actingAs($u)->get('/prospecting'); $token=session()->token();
  $this->withHeader('X-CSRF-TOKEN',$token)->postJson('/api/prospecting/jobs',['keywords'=>['dentista'],'search_terms'=>['dentista'],'location'=>'São Paulo, SP','max_results'=>20,'lat'=>null,'lon'=>null,'max_time'=>180,'depth'=>1,'email'=>true])->assertStatus(202)->assertJsonPath('status','pending');
  Queue::assertPushed(\App\Jobs\RunScrapingJob::class);
 }
 public function test_apify_payload_contains_search_and_location_fields(): void {
  $u=User::factory()->create(['email_verified_at'=>now(),'apify_api_token'=>'test-token']);
  $job=ProspectingJob::create(['user_id'=>$u->id,'keywords'=>['contabilidade'],'location'=>'São Paulo, SP','search_parameters'=>['location'=>'São Paulo, SP','max_results'=>20,'lang'=>'pt','email'=>true],'status'=>'pending']);
  Http::fake(function ($request) { $this->assertStringContainsString('/acts/compass~crawler-google-places/runs', $request->url()); $body=$request->data(); $this->assertSame(['contabilidade'],$body['searchStringsArray']); $this->assertSame('São Paulo, SP',$body['locationQuery']); $this->assertSame(20,$body['maxCrawledPlacesPerSearch']); $this->assertSame('pt-BR',$body['language']); $this->assertTrue($body['scrapeContacts']); return Http::response(['data'=>['id'=>'run-1','defaultDatasetId'=>'dataset-1']],201); });
  $this->assertSame('apify:run-1:dataset-1', app(ScraperClient::class)->create($job));
 }
 private function job(array $extra=[]): ProspectingJob { $u=User::factory()->create(['email_verified_at'=>now()]); \DB::table('scraper_capacity')->update(['used'=>1]); return ProspectingJob::create(array_merge(['user_id'=>$u->id,'keywords'=>['cafe'],'latitude'=>1,'longitude'=>2,'status'=>'working','scraper_job_id'=>'remote-1','scraper_slot_acquired'=>true,'started_at'=>now()],$extra)); }
 public function test_polling_imports_completed_csv_idempotently(): void {
  $job=$this->job(['search_parameters'=>['max_results'=>1]]); Http::fake(['*/api/v1/jobs/remote-1'=>Http::response(['Status'=>'ok','Progress'=>100]),'*/api/v1/jobs/remote-1/download'=>Http::response("title,place_id\nCafe,p1\n")]);
  (new PollScrapingJob($job->id))->handle(); (new PollScrapingJob($job->id))->handle();
  $this->assertDatabaseHas('prospecting_jobs',['id'=>$job->id,'status'=>'ok','progress'=>100]); $this->assertSame(1,Lead::count());
 }
 public function test_existing_leads_are_not_attached_to_a_repeated_search(): void {
  $job=$this->job();
  Lead::create(['user_id'=>$job->user_id,'name'=>'Already saved','external_place_id'=>'p1','source'=>'manual']);
  Http::fake(['*/download'=>Http::response("title,place_id\nAlready saved,p1\nNew place,p2\n")]);
  $outcome=app(ScraperClient::class)->import($job);
  $this->assertSame(1,$outcome->accepted); $this->assertSame(1,$outcome->updated);
  $this->assertCount(1,$job->fresh()->leads); $this->assertDatabaseHas('leads',['external_place_id'=>'p1','prospecting_job_id'=>null]);
 }
 public function test_polling_marks_remote_failure(): void {
  $job=$this->job(); Http::fake(['*'=>Http::response(['Status'=>'failed'])]);
  (new PollScrapingJob($job->id))->handle();
  $this->assertDatabaseHas('prospecting_jobs',['id'=>$job->id,'status'=>'failed']);
 }
 public function test_failed_poll_preserves_capacity_when_external_state_is_unknown(): void {
  $job=$this->job(); (new PollScrapingJob($job->id))->failed(new \RuntimeException('timeout'));
  $this->assertDatabaseHas('prospecting_jobs',['id'=>$job->id,'status'=>'unknown','scraper_job_id'=>'remote-1','scraper_slot_acquired'=>1]);
  $this->assertSame(1,(int)\DB::table('scraper_capacity')->where('id',1)->value('used'));
 }
 public function test_stale_claim_token_cannot_update_new_owner(): void {
  $job=$this->job(['polling_claimed_at'=>now()]); \DB::table('prospecting_jobs')->where('id',$job->id)->update(['polling_claim_token'=>'token-b']);
  $affected=ProspectingJob::whereKey($job->id)->where('polling_claim_token','token-a')->update(['progress'=>99]);
  $this->assertSame(0,$affected); $this->assertDatabaseHas('prospecting_jobs',['id'=>$job->id,'polling_claim_token'=>'token-b','progress'=>0]);
 }
 public function test_claim_renewal_requires_current_token(): void {
  $job=$this->job(['polling_claimed_at'=>now()->subMinutes(9)]); \DB::table('prospecting_jobs')->where('id',$job->id)->update(['polling_claim_token'=>'token-b']);
  $valid=ProspectingJob::whereKey($job->id)->where('polling_claim_token','token-b')->update(['polling_claimed_at'=>now()]);
  $stale=ProspectingJob::whereKey($job->id)->where('polling_claim_token','token-a')->update(['polling_claimed_at'=>now()]);
  $this->assertSame(1,$valid); $this->assertSame(0,$stale); $this->assertDatabaseHas('prospecting_jobs',['id'=>$job->id,'polling_claim_token'=>'token-b']);
 }

 public function test_submission_failure_keeps_capacity_and_marks_unknown(): void {
  $u=User::factory()->create(['email_verified_at'=>now(),'apify_api_token'=>'test-token']); $job=ProspectingJob::create(['user_id'=>$u->id,'keywords'=>['cafe'],'latitude'=>1,'longitude'=>2,'status'=>'pending']);
  Http::fake(fn()=>throw new \RuntimeException('ambiguous timeout'));
  (new \App\Jobs\RunScrapingJob($job->id))->handle();
  $this->assertDatabaseHas('prospecting_jobs',['id'=>$job->id,'status'=>'unknown','scraper_slot_acquired'=>1,'scraper_job_id'=>null]);
  $this->assertSame(1,(int)\DB::table('scraper_capacity')->where('id',1)->value('used'));
 }

 public function test_completed_job_does_not_post_again(): void {
  $job=$this->job(['status'=>'ok','scraper_job_id'=>'remote-complete','scraper_slot_acquired'=>false]); Http::fake();
  (new \App\Jobs\RunScrapingJob($job->id))->handle();
  Http::assertNothingSent();
 }

 public function test_csv_parser_handles_quotes_cid_fallback_and_reports_rejections(): void {
  $job=$this->job(); Http::fake(['*/download'=>Http::response("title,place_id,cid\n\"Cafe, Centro\",,cid-1\nMissing,,\n")]);
  $outcome=app(ScraperClient::class)->import($job);
  $this->assertTrue($outcome->complete); $this->assertSame(1,$outcome->accepted); $this->assertSame(1,$outcome->rejected); $this->assertDatabaseHas('leads',['external_place_id'=>'cid-1','name'=>'Cafe, Centro']);
 }

 public function test_lost_owner_stops_import_and_is_not_complete(): void {
  $job=$this->job(['polling_claim_token'=>'owner-a']); Http::fake(['*/download'=>Http::response("title,place_id\nCafe,p1\n")]);
  $outcome=app(ScraperClient::class)->import($job,'owner-b',fn()=>false);
  $this->assertFalse($outcome->complete); $this->assertTrue($outcome->ownershipLost); $this->assertSame(0,Lead::count());
 }
}
