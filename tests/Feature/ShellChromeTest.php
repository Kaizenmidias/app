<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShellChromeTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_page_has_clean_shared_shell_and_personal_greeting(): void
    {
        $user = User::factory()->create([
            'name' => 'Ana Beatriz Silva',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/minha-conta');

        $response->assertOk()
            ->assertSee('Bem-vindo,')
            ->assertSee('Ana')
            ->assertSee('account.css?v=')
            ->assertSee('account-page')
            ->assertSee('module-card')
            ->assertSee('Minha conta')
            ->assertSee('Kaizen Mídias')
            ->assertDontSee('ide&gt;')
            ->assertDontSee('Kaizen MÃ­dias');
    }
}
