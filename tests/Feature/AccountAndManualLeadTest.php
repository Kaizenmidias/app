<?php
namespace Tests\Feature;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
class AccountAndManualLeadTest extends TestCase { use RefreshDatabase;
 public function test_user_can_update_own_profile_and_password(): void { $u=User::factory()->create();$this->actingAs($u)->put('/minha-conta',['name'=>'Novo Nome','email'=>$u->email])->assertRedirect()->assertSessionHas('status');$this->assertDatabaseHas('users',['id'=>$u->id,'name'=>'Novo Nome']);$this->actingAs($u)->put('/minha-conta/senha',['current_password'=>'password','password'=>'NewSecurePassword123!','password_confirmation'=>'NewSecurePassword123!'])->assertRedirect()->assertSessionHas('status');$this->assertTrue(Hash::check('NewSecurePassword123!', $u->refresh()->password)); }
 public function test_manual_lead_is_owned_and_uses_existing_json_metadata(): void { $u=User::factory()->create();$this->actingAs($u)->post('/leads',['name'=>'Contato Manual','company'=>'Empresa','email'=>'manual@example.test','observations'=>'Nota'])->assertRedirect()->assertSessionHas('status');$lead=Lead::where('name','Contato Manual')->firstOrFail();$this->assertSame($u->id,$lead->user_id);$this->assertSame('manual',$lead->source);$this->assertSame('Empresa',$lead->raw_data['manual_company']);$this->assertSame('Nota',$lead->raw_data['manual_observations']); }
 public function test_placeholder_modules_are_authenticated(): void { $this->get('/chats')->assertRedirect('/login');$this->actingAs(User::factory()->create())->get('/chats')->assertOk()->assertSee('Em desenvolvimento'); }
}
