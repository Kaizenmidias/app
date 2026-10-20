<?php
namespace Tests\Feature;
use App\Models\{Lead,User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class LeadsPageTest extends TestCase { use RefreshDatabase;
 public function test_owner_can_view_paginated_leads(): void { $u=User::factory()->create(['email_verified_at'=>now()]); Lead::create(['user_id'=>$u->id,'name'=>'Empresa Demo']); $this->actingAs($u)->get('/leads')->assertOk()->assertSee('Empresa Demo'); }
 public function test_guest_is_redirected(): void { $this->get('/leads')->assertRedirect('/login'); }
 public function test_leads_are_isolated(): void { $a=User::factory()->create(['email_verified_at'=>now()]); $b=User::factory()->create(['email_verified_at'=>now()]); Lead::create(['user_id'=>$a->id,'name'=>'Privado A']); $this->actingAs($b)->get('/leads')->assertOk()->assertDontSee('Privado A'); }
}
