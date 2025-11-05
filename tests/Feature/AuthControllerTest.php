<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_register_successfully()
    {
        $payload = [
            'username' => 'test_user',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'token',
                'user',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => $payload['email'],
        ]);
    }

    #[Test]
    public function user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user['email'],
            'password' => "password123",
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'token',
            ]);
    }


    #[Test]
    public function login_fails_with_invalid_password()
    {
        $user = User::factory()->create([
            'email' => 'fail@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user['email'],
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(200)
             ->assertJson([
                 'message' => 'The provided credentials are incorrect.',
             ]);
    }

    #[Test]
    public function authenticated_user_can_logout()
    {
        $user = User::factory()->create();
        $token = $user->createToken('api-token', ['*'], now()->addMinutes(30))->plainTextToken;

        $response = $this->withHeader(
            'Authorization',
            'Bearer ' . $token
        )->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'You have been logged out.',
            ]);
    }

    #[Test]
    public function unauthenticated_user_cannot_logout()
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated or token expired.',
            ]);
    }
}
