<?php
namespace Tests\Feature;
use App\Models\User; use Illuminate\Foundation\Testing\RefreshDatabase; use Tests\TestCase;
class DashboardShellTest extends TestCase { use RefreshDatabase; public function test_dashboard_uses_shared_sidebar_and_overview_metrics(): void { $r=$this->actingAs(User::factory()->create())->get('/dashboard'); $r->assertOk()->assertSee('scraper-shell-sidebar')->assertSee('Minha conta')->assertSee('Leads prospectados')->assertSee('Atividade comercial')->assertSee('dashboard-overview.css?v=',false); $r->assertDontSee('Nova busca')->assertDontSee('Suas buscas'); } }
