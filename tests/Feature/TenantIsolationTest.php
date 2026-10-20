<?php
namespace Tests\Feature;
use App\Models\ProspectingJob;
use App\Models\Lead;
use App\Models\User;
use App\Models\LeadNote;
use App\Models\ProspectingExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;
    public function test_partner_cannot_read_another_partners_job(): void
    {
        $one=User::factory()->create(['email_verified_at'=>now()]); $two=User::factory()->create(['email_verified_at'=>now()]);
        $job=ProspectingJob::create(['user_id'=>$one->id,'keywords'=>['dentistas'],'status'=>'ok']);
        $this->actingAs($two)->getJson('/api/prospecting/jobs/'.$job->id)->assertNotFound();
    }
    public function test_partner_cannot_update_another_partners_lead(): void
    {
        $one=User::factory()->create(['email_verified_at'=>now()]); $two=User::factory()->create(['email_verified_at'=>now()]);
        $lead=Lead::create(['user_id'=>$one->id,'name'=>'Private lead']);
        $this->actingAs($two)->putJson('/api/leads/'.$lead->id,['name'=>'Changed'])->assertNotFound();
    }
    public function test_partner_cannot_access_another_partners_note_or_export(): void
    {
        $one=User::factory()->create(['email_verified_at'=>now()]); $two=User::factory()->create(['email_verified_at'=>now()]);
        $job=ProspectingJob::create(['user_id'=>$one->id,'keywords'=>['cafes'],'status'=>'ok']);
        $lead=Lead::create(['user_id'=>$one->id,'prospecting_job_id'=>$job->id,'name'=>'Private']);
        $note=LeadNote::create(['user_id'=>$one->id,'lead_id'=>$lead->id,'content'=>'private']);
        $this->actingAs($two)->postJson('/api/leads/'.$lead->id.'/notes',['content'=>'x'])->assertNotFound();
        $this->actingAs($two)->putJson('/api/notes/'.$note->id,['content'=>'x'])->assertNotFound();
        $this->actingAs($two)->get('/api/prospecting/jobs/'.$job->id.'/export')->assertNotFound();
    }
    public function test_owner_can_create_and_download_private_export(): void
    {
        $user=User::factory()->create(['email_verified_at'=>now()]);
        $job=ProspectingJob::create(['user_id'=>$user->id,'keywords'=>['cafes'],'status'=>'ok']);
        Lead::create(['user_id'=>$user->id,'prospecting_job_id'=>$job->id,'name'=>'Cafe']);
        $this->actingAs($user)->get('/api/prospecting/jobs/'.$job->id.'/export')->assertOk()->assertHeader('content-type','text/csv; charset=UTF-8');
        $this->assertDatabaseHas('prospecting_exports',['user_id'=>$user->id,'prospecting_job_id'=>$job->id,'format'=>'csv']);
    }
}
