<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\TestCase;

class ProspectingRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_receives_original_file_response(): void
    {
        $user = User::factory()->create(['email_verified_at' => now(), 'status' => 'active']);

        $response = $this->actingAs($user)->get('/prospecting');

        $response->assertOk();
        $this->assertInstanceOf(BinaryFileResponse::class, $response->baseResponse);
        $this->assertSame(
            realpath(resource_path('views/prospecting.html')),
            $response->baseResponse->getFile()->getRealPath(),
        );
    }
}
