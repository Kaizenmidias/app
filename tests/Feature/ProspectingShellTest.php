<?php
namespace Tests\Feature;
use App\Models\User; use Illuminate\Foundation\Testing\RefreshDatabase; use Tests\TestCase;
class ProspectingShellTest extends TestCase { use RefreshDatabase;
 public function test_prospecting_requires_authentication(): void { $this->get('/prospecting')->assertRedirect('/login'); }
 public function test_authenticated_prospecting_keeps_functional_panel_and_shell(): void { $r=$this->actingAs(User::factory()->create())->get('/prospecting');$r->assertOk()->assertSee('prospecting-shell-sidebar')->assertSee('prospecting-functional-panel')->assertSee('id="map"',false)->assertSee('id="search-form"',false)->assertSee('id="keyword"',false)->assertSee('id="jobs"',false)->assertSee('/app/app.js',false)->assertSee('class="is-active"',false);$this->assertStringNotContainsString('display:none!important;visibility:hidden!important',$r->getContent()); }
}
