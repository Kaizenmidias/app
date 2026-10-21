<?php
namespace Tests\Feature;
use App\Models\User; use Illuminate\Foundation\Testing\RefreshDatabase; use Tests\TestCase;
class DashboardShellTest extends TestCase { use RefreshDatabase;
 public function test_dashboard_uses_shared_sidebar_and_preserves_panel_controls(): void { $r=$this->actingAs(User::factory()->create())->get('/dashboard');$r->assertOk()->assertSee('scraper-shell-sidebar')->assertSee('Minha conta')->assertSee('Chats')->assertSee('id="search-form"',false)->assertSee('id="jobs"',false)->assertSee('dashboard-legacy-sidebar')->assertSee('scraper-shell.css?v=',false)->assertSee('dashboard-shell.css?v=',false);$this->assertStringContainsString('dashboard-legacy-sidebar" style="display:none!important;visibility:hidden!important', $r->getContent()); }
}
