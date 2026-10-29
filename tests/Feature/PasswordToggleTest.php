<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordToggleTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_and_account_password_fields_render_accessible_eye_controls(): void
    {
        $this->get('/login')->assertOk()->assertSee('password-toggle')->assertSee('password-toggle.js');

        $user = User::factory()->create();
        $this->actingAs($user)->get('/minha-conta')->assertOk()
            ->assertSee('aria-controls="current-password"', false)
            ->assertSee('aria-controls="new-password"', false)
            ->assertSee('aria-controls="password-confirmation"', false);
    }

    public function test_admin_password_fields_render_unique_controls(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get('/admin/users')->assertOk()
            ->assertSee('admin-create-password', false)
            ->assertSee('admin-create-password-confirmation', false);
    }
}
