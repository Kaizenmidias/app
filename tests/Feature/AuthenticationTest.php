<?php
namespace Tests\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Notification;
class AuthenticationTest extends TestCase
{
    use RefreshDatabase;
    public function test_public_registration_creates_only_partner(): void
    {
        $this->post('/register',['name'=>'Partner One','email'=>'one@example.test','password'=>'VeryStrongPassword123!','password_confirmation'=>'VeryStrongPassword123!','terms'=>'on'])->assertRedirect('/login');
        $this->assertDatabaseHas('users',['email'=>'one@example.test','role'=>'partner']);
    }
    public function test_unverified_user_cannot_access_dashboard(): void
    {
        $user=User::factory()->create(['email_verified_at'=>null]); $this->actingAs($user)->get('/dashboard')->assertRedirect('/email/verify');
    }
    public function test_partner_cannot_access_admin(): void
    {
        $user=User::factory()->create(['email_verified_at'=>now(),'role'=>'partner']); $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_verified_active_user_dashboard_redirects_to_original_prospecting(): void
    {
        $user = User::factory()->create(['email_verified_at' => now(), 'role' => 'partner', 'status' => 'active']);

        $this->actingAs($user)->get('/dashboard')->assertRedirect('/prospecting');
    }
    public function test_password_reset_request_is_generic_for_unknown_email(): void
    {
        Notification::fake();
        $response=$this->post('/forgot-password',['email'=>'unknown@example.test']);
        $response->assertSessionHas('status');
    }
}
