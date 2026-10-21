<?php
namespace Tests\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class ProspectingShellTest extends TestCase { use RefreshDatabase;
 public function test_prospecting_requires_authentication(): void { $this->get('/prospecting')->assertRedirect('/login'); }
 public function test_authenticated_prospecting_keeps_scraper_contract_and_shell(): void { $u=User::factory()->create();$this->actingAs($u)->get('/prospecting')->assertOk()->assertSee('prospecting-shell-sidebar')->assertSee('id="map"',false)->assertSee('id="search-form"',false)->assertSee('id="keyword"',false)->assertSee('/app/app.js',false)->assertSee('class="is-active"',false); }
}
