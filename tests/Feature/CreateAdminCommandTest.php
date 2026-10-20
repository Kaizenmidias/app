<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_creates_verified_admin_with_hashed_password_once(): void
    {
        $this->artisan('kaizen:create-admin', ['email' => 'admin@example.test'])
            ->expectsQuestion('Nome completo', 'Test Administrator')
            ->expectsQuestion('Senha (não será exibida)', 'VeryStrongPassword123!')
            ->assertExitCode(0);

        $user = User::where('email', 'admin@example.test')->firstOrFail();
        $this->assertSame('admin', $user->role);
        $this->assertSame('active', $user->status);
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertTrue(Hash::check('VeryStrongPassword123!', $user->password));
        $this->assertSame(1, User::where('email', 'admin@example.test')->count());
    }
}
