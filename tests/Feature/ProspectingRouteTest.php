<?php
namespace Tests\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
class ProspectingRouteTest extends TestCase
{
    use RefreshDatabase;
    public function test_authenticated_user_receives_original_file_response(): void
    {
        $user = User::factory()->create(['email_verified_at' => now(), 'status' => 'active']);
        $response = $this->actingAs($user)->get('/prospecting');
        $response->assertOk();
        $this->assertInstanceOf(BinaryFileResponse::class, $response->baseResponse);
        $this->assertSame(realpath(resource_path('views/prospecting.html')), $response->baseResponse->getFile()->getRealPath());
    }
    public function test_authenticated_user_receives_original_panel(): void { $user=User::factory()->create(['email_verified_at'=>now(),'status'=>'active']); $response=$this->actingAs($user)->get('/painel'); $response->assertOk()->assertSee('href="/leads"', false)->assertSee('href="/pipeline"', false); }
    public function test_dashboard_and_panel_render_the_same_protected_view(): void { $user=User::factory()->create(['email_verified_at'=>now(),'status'=>'active']); $dashboard=$this->actingAs($user)->get('/dashboard'); $panel=$this->actingAs($user)->get('/painel'); $this->assertSame($dashboard->getContent(), $panel->getContent()); }
    public function test_compact_credits_are_removed_and_license_attribution_remains(): void {
        $panel = file_get_contents(resource_path('views/panel.blade.php'));
        $prospecting = file_get_contents(resource_path('views/prospecting.html'));
        $this->assertStringNotContainsString('class="credits compact"', $panel);
        $this->assertStringContainsString('licença MIT', $panel);
        $this->assertStringContainsString('Mahanaicoach', $panel);
        $this->assertStringNotContainsString('credit-btn', $prospecting);
        $this->assertStringNotContainsString('Dar estrela', $prospecting);
        $this->assertStringNotContainsString('Apoiar o autor', $prospecting);
        $this->assertStringContainsString('licença MIT', $prospecting);
    }
    public function test_protected_panel_source_is_outside_public_runtime(): void { $this->assertFileDoesNotExist(public_path('app/painel.html')); $this->assertFileExists(resource_path('views/panel.blade.php')); }
    public function test_guest_cannot_access_original_panel(): void { $this->get('/painel')->assertRedirect('/login'); }
}
