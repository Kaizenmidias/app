<?php
namespace Tests\Feature;
use App\Console\Commands\ReconcileScraperJobs;
use App\Jobs\{PollScrapingJob,RunScrapingJob};
use App\Models\{Lead,LeadNote,ProspectingJob,User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ScraperRegressionTest extends TestCase
{
 use RefreshDatabase;

 private function job(array $extra=[]): ProspectingJob {
  $u=User::factory()->create(['email_verified_at'=>now()]);
  DB::table('scraper_capacity')->update(['used'=>1]);
  return ProspectingJob::create(array_merge(['user_id'=>$u->id,'keywords'=>['cafe'],'latitude'=>1,'longitude'=>2,'status'=>'working','scraper_job_id'=>'remote-1','scraper_slot_acquired'=>true,'started_at'=>now()],$extra));
 }

 public function test_completed_poll_clears_persistent_claim_and_repeated_poll_is_noop(): void {
  $job=$this->job(['search_parameters'=>['max_results'=>1]]);
  Http::fake(['*/api/v1/jobs/remote-1'=>Http::response(['Status'=>'ok','Progress'=>100]),'*/api/v1/jobs/remote-1/download'=>Http::response("title,place_id
Cafe,p1
")]);
  (new PollScrapingJob($job->id))->handle();
  $this->assertDatabaseHas('prospecting_jobs',['id'=>$job->id,'status'=>'ok','polling_claimed_at'=>null]);
  (new PollScrapingJob($job->id))->handle();
  $this->assertSame(1,Lead::count());
 }

 public function test_active_claim_prevents_second_poll_and_expired_claim_is_recoverable(): void {
  $job=$this->job(['polling_claimed_at'=>now()]);
  Http::fake(['*'=>Http::response(['Status'=>'working','Progress'=>5])]);
  (new PollScrapingJob($job->id))->handle();
  Http::assertSentCount(0);
  $job->update(['polling_claimed_at'=>now()->subMinutes(11)]);
  (new PollScrapingJob($job->id))->handle();
  Http::assertSentCount(1);
 }

 public function test_unknown_command_only_lists_and_does_not_change_jobs(): void {
  $job=$this->job(['status'=>'unknown','error_message'=>'network','scraper_job_id'=>'remote-x']);
  $before=DB::table('scraper_capacity')->where('id',1)->value('used');
  $this->artisan('scraper:unknown')->expectsOutputToContain('remote-x')->assertExitCode(0);
  $this->assertDatabaseHas('prospecting_jobs',['id'=>$job->id,'status'=>'unknown','scraper_slot_acquired'=>1]);
  $this->assertSame($before,DB::table('scraper_capacity')->where('id',1)->value('used'));
 }
 public function test_unknown_job_without_remote_id_can_be_deleted(): void {
  $job=$this->job(['status'=>'unknown','scraper_job_id'=>null]);
  $this->get('/login'); $token=csrf_token();
  $this->actingAs($job->user)->withHeader('X-CSRF-TOKEN', $token)->delete('/api/v1/jobs/'.$job->id)->assertNoContent();
  $this->assertSoftDeleted('prospecting_jobs',['id'=>$job->id]);
 }
 public function test_pending_job_without_remote_id_can_be_deleted(): void {
  $job=$this->job(['status'=>'pending','scraper_job_id'=>null]);
  $this->get('/login'); $token=csrf_token();
  $this->actingAs($job->user)->withHeader('X-CSRF-TOKEN', $token)->delete('/api/v1/jobs/'.$job->id)->assertNoContent();
  $this->assertSoftDeleted('prospecting_jobs',['id'=>$job->id]);
 }
 public function test_reconcile_requeues_unknown_with_external_id_without_posting(): void {
  Queue::fake(); $job=$this->job(['status'=>'unknown','polling_claimed_at'=>now()->subMinutes(11)]);
  $this->artisan('scraper:reconcile')->assertExitCode(0); Queue::assertPushed(PollScrapingJob::class,fn($j)=>$j->jobId===$job->id);
 }
 public function test_reconcile_does_not_requeue_unknown_without_external_id(): void {
  Queue::fake(); $job=$this->job(['status'=>'unknown','scraper_job_id'=>null,'polling_claimed_at'=>now()->subMinutes(11)]);
  $this->artisan('scraper:reconcile')->assertExitCode(0); Queue::assertNotPushed(PollScrapingJob::class);
 }
 public function test_reconcile_closes_stale_unknown_without_external_id_and_releases_capacity(): void {
  Queue::fake(); $job=$this->job(['status'=>'unknown','scraper_job_id'=>null]); DB::table('prospecting_jobs')->where('id',$job->id)->update(['updated_at'=>now()->subMinutes(20)]);
  $job->attempts()->create(['provider'=>'apify','status'=>'unknown','started_at'=>now()->subMinutes(20)]);
  Artisan::call('scraper:reconcile');
  $this->assertDatabaseHas('prospecting_jobs',['id'=>$job->id,'status'=>'failed','scraper_slot_acquired'=>0]);
  $this->assertDatabaseHas('prospecting_job_attempts',['prospecting_job_id'=>$job->id,'status'=>'reconciled_failed']);
  $this->assertSame(0,(int)DB::table('scraper_capacity')->where('id',1)->value('used'));
 }

 public function test_lines_without_external_identifier_are_ignored(): void {
  $job=$this->job();
  Http::fake(['*/api/v1/jobs/remote-1'=>Http::response(['Status'=>'ok']),'*/api/v1/jobs/remote-1/download'=>Http::response("title,address
Unknown,Somewhere
")]);
  (new PollScrapingJob($job->id))->handle();
  $this->assertSame(0,Lead::count());
 }

 public function test_user_id_payload_cannot_cross_tenant_or_link_note_to_foreign_lead(): void {
  $one=User::factory()->create(['email_verified_at'=>now()]); $two=User::factory()->create(['email_verified_at'=>now()]);
  $lead=Lead::create(['user_id'=>$one->id,'name'=>'Private']);
  $this->actingAs($two)->postJson('/api/leads/'.$lead->id.'/notes',['user_id'=>$one->id,'content'=>'x'])->assertForbidden();
  $this->assertDatabaseCount('lead_notes',0);
 }

 public function test_foreign_job_export_and_status_are_denied(): void {
  $one=User::factory()->create(['email_verified_at'=>now()]); $two=User::factory()->create(['email_verified_at'=>now()]);
  $job=ProspectingJob::create(['user_id'=>$one->id,'keywords'=>['x'],'status'=>'ok']);
  $this->actingAs($two)->getJson('/api/prospecting/jobs/'.$job->id)->assertForbidden();
  $this->actingAs($two)->get('/api/prospecting/jobs/'.$job->id.'/export')->assertForbidden();
 }

 public function test_legacy_status_does_not_call_scraper(): void {
  $job=$this->job(['status'=>'working']);
  Http::fake();
  $u=User::find($job->user_id);
  $this->actingAs($u)->getJson('/api/v1/jobs/'.$job->id)->assertOk();
  Http::assertNothingSent();
  $this->assertDatabaseHas('prospecting_jobs',['id'=>$job->id,'status'=>'working']);
 }

 public function test_capacity_release_is_idempotent_after_completion(): void {
  $job=$this->job();
  Http::fake(['*/api/v1/jobs/remote-1'=>Http::response(['Status'=>'failed'])]);
  (new PollScrapingJob($job->id))->handle();
  $this->assertSame(0,(int)DB::table('scraper_capacity')->where('id',1)->value('used'));
  (new PollScrapingJob($job->id))->handle();
  $this->assertSame(0,(int)DB::table('scraper_capacity')->where('id',1)->value('used'));
 }

 public function test_expired_local_deadline_still_accepts_external_terminal_status(): void {
  $job=$this->job(['started_at'=>now()->subHours(2)]);
  Http::fake(['*/api/v1/jobs/remote-1'=>Http::response(['Status'=>'failed','Progress'=>100])]);
  (new PollScrapingJob($job->id))->handle();
  $this->assertDatabaseHas('prospecting_jobs',['id'=>$job->id,'status'=>'failed','scraper_slot_acquired'=>0]);
  Http::assertSentCount(1);
 }

 public function test_repeated_cancellation_is_reconciled_without_premature_release(): void {
  $job=$this->job(['status'=>'cancel_requested','cancel_requested_at'=>now()]);
  Http::fake(['*/api/v1/jobs/remote-1'=>Http::response(['Status'=>'working','Progress'=>4])]);
  (new PollScrapingJob($job->id))->handle();
  $this->assertDatabaseHas('prospecting_jobs',['id'=>$job->id,'status'=>'cancel_requested','scraper_slot_acquired'=>1]);
 }
 public function test_duplicate_submission_is_rejected_while_previous_job_is_active(): void {
  $u=User::factory()->create(['email_verified_at'=>now(),'status'=>'active','apify_api_token'=>'test-token']); Queue::fake(); $this->actingAs($u)->get('/prospecting'); $token=session()->token();
  $payload=['keywords'=>['dentista'],'search_terms'=>['dentista'],'location'=>'São Paulo, SP','max_results'=>20,'lat'=>null,'lon'=>null,'max_time'=>180,'depth'=>1,'email'=>true];
  $this->withHeader('X-CSRF-TOKEN',$token)->postJson('/api/prospecting/jobs',$payload)->assertStatus(202);
  $this->withHeader('X-CSRF-TOKEN',$token)->postJson('/api/prospecting/jobs',$payload)->assertStatus(409)->assertJsonPath('message','Essa busca já está em andamento.');
  Queue::assertPushed(RunScrapingJob::class,1);
 }
}





