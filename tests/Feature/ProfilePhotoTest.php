<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfilePhotoTest extends TestCase
{
    use RefreshDatabase;

    private function png(string $name): UploadedFile
    {
        $bytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');
        return UploadedFile::fake()->createWithContent($name, $bytes);
    }

    public function test_user_can_upload_replace_and_remove_own_photo(): void
    {
        Storage::fake('local');
        $user = User::factory()->create(['email_verified_at' => now()]);

        $first = $this->png('first.png');
        $response = $this->actingAs($user)->post('/minha-conta/foto', ['photo' => $first]);
        $response->assertRedirect()->assertSessionHas('status');
        $path = $user->refresh()->profile_photo_path;
        $this->assertNotEmpty($path);
        Storage::disk('local')->assertExists($path);

        $second = $this->png('second.png');
        $this->actingAs($user)->post('/minha-conta/foto', ['photo' => $second])->assertRedirect();
        $newPath = $user->refresh()->profile_photo_path;
        $this->assertNotSame($path, $newPath);
        Storage::disk('local')->assertMissing($path);
        Storage::disk('local')->assertExists($newPath);

        $this->actingAs($user)->delete('/minha-conta/foto')->assertRedirect();
        $this->assertNull($user->refresh()->profile_photo_path);
        Storage::disk('local')->assertMissing($newPath);
    }

    public function test_invalid_photo_is_rejected_and_photo_route_is_private(): void
    {
        $this->get('/minha-conta/foto')->assertRedirect('/login');
        Storage::fake('local');
        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user)->post('/minha-conta/foto', ['photo' => UploadedFile::fake()->create('note.txt', 10, 'text/plain')])->assertSessionHasErrors('photo');
    }

    public function test_photo_form_starts_with_save_action_hidden(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)->get('/minha-conta')
            ->assertOk()
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('id="profile-photo-input"', false)
            ->assertSee('id="save-photo-button" type="submit" class="button button-primary" hidden', false);
    }

    public function test_administrator_can_assign_photo_to_managed_user(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($admin)->put('/admin/users/'.$user->id, [
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'partner',
            'status' => 'active',
            'module_access' => ['dashboard'],
            'password' => '',
            'password_confirmation' => '',
            'profile_photo' => $this->png('managed.png'),
        ])->assertRedirect('/admin/users');

        $path = $user->refresh()->profile_photo_path;
        $this->assertNotEmpty($path);
        Storage::disk('local')->assertExists($path);
    }
}
