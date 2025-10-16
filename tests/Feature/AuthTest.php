<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Sanctum basic login tests
 */
class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_login_and_get_token(): void
    {
        $user = User::factory()->create([
            'email' => 'username@tierone.com',
            'password' => Hash::make('my-secret'),
        ]);

        $resp = $this->postJson('/api/login', [
            'email' => 'username@tierone.com',
            'password' => 'my-secret',
        ]);

        $resp->assertOk()
            ->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token']);
    }

    /** @test */
    public function reject_invalid_credentials(): void
    {
        $resp = $this->postJson('/api/login', [
            'email' => 'nope@example.com',
            'password' => 'wrong-password',
        ]);

        $resp->assertStatus(401);
    }
}
