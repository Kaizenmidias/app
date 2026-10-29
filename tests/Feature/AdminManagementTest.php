<?php

namespace Tests\Feature;

use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_administrator_can_open_user_management(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user)->get('/admin/users')->assertForbidden();
        $this->actingAs($admin)->get('/admin/users')->assertOk()->assertSee('Usuários')->assertSee('Novo usuário')->assertSee($user->email);
    }

    public function test_administrator_creates_user_with_selected_modules_and_hashed_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Usuário Gerenciado', 'email' => 'managed@example.test',
            'password' => 'VerySecurePassword123!', 'password_confirmation' => 'VerySecurePassword123!',
            'role' => 'partner', 'status' => 'active', 'module_access' => ['dashboard', 'leads'],
        ])->assertRedirect('/admin/users');
        $user = User::where('email', 'managed@example.test')->firstOrFail();
        $this->assertSame(['dashboard', 'leads'], $user->module_access);
        $this->assertTrue(Hash::check('VerySecurePassword123!', $user->password));
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertDatabaseHas('admin_audit_logs', ['admin_user_id' => $admin->id, 'action' => 'user.created', 'target_id' => $user->id]);
    }

    public function test_module_access_is_hidden_and_enforced_server_side(): void
    {
        $user = User::factory()->create(['module_access' => ['dashboard', 'leads']]);
        $this->actingAs($user)->get('/dashboard')->assertOk()->assertDontSee('Prospecção');
        $this->actingAs($user)->get('/leads')->assertOk();
        $this->actingAs($user)->get('/prospecting')->assertForbidden();
        $this->actingAs($user)->get('/pipeline')->assertForbidden();
        $this->actingAs($user)->get('/minha-conta')->assertOk();
    }

    public function test_administrator_can_update_identity_access_and_password_without_exposing_hash(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $this->actingAs($admin)->put('/admin/users/'.$user->id, [
            'name' => 'Nome Atualizado', 'email' => $user->email,
            'password' => 'AnotherSecurePassword123!', 'password_confirmation' => 'AnotherSecurePassword123!',
            'role' => 'partner', 'status' => 'blocked', 'module_access' => ['pipeline'],
        ])->assertRedirect('/admin/users');
        $user->refresh();
        $this->assertSame('Nome Atualizado', $user->name);
        $this->assertSame('blocked', $user->status);
        $this->assertSame(['pipeline'], $user->module_access);
        $this->assertTrue(Hash::check('AnotherSecurePassword123!', $user->password));
        $this->actingAs($admin)->get('/admin/users')->assertOk()->assertDontSee($user->password);
    }

    public function test_last_active_administrator_cannot_be_blocked_or_demoted(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->actingAs($admin)->post('/admin/users/'.$admin->id.'/toggle')->assertForbidden();
        $this->assertTrue($admin->fresh()->isActive());
        $this->actingAs($admin)->put('/admin/users/'.$admin->id, [
            'name' => $admin->name, 'email' => $admin->email, 'role' => 'partner', 'status' => 'active',
            'module_access' => ['dashboard'], 'password' => '', 'password_confirmation' => '',
        ])->assertForbidden();
        $this->assertTrue($admin->fresh()->isAdmin());
        $this->assertSame(0, AdminAuditLog::count());
    }
}
