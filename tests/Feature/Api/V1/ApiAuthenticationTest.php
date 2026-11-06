<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_an_employee_and_returns_a_bearer_token(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'first_name' => 'Taylor',
            'last_name' => 'Reed',
            'email' => 'taylor@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'terms' => true,
            'device_name' => 'Taylor phone',
            'is_admin' => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Taylor Reed')
            ->assertJsonPath('data.role', 'employee')
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonStructure(['data' => ['id', 'name', 'email', 'role'], 'token']);

        $this->assertDatabaseHas('users', [
            'email' => 'taylor@example.com',
            'is_admin' => false,
        ]);
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_login_token_authenticates_the_user_and_logout_revokes_it(): void
    {
        $user = User::factory()->create([
            'email' => 'taylor@example.com',
            'password' => 'secret-password',
        ]);
        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'taylor@example.com',
            'password' => 'secret-password',
            'device_name' => 'Test phone',
        ]);
        $login->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('token_type', 'Bearer');
        $token = $login->json('token');

        $this->withToken($token)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id);

        $this->withToken($token)
            ->postJson('/api/v1/auth/logout')
            ->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_invalid_credentials_return_unauthorized(): void
    {
        $user = User::factory()->create(['password' => 'correct-password']);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'incorrect-password',
            'device_name' => 'Test phone',
        ])->assertUnauthorized();
    }

    public function test_api_route_accepts_the_existing_authenticated_session(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id);
    }

    public function test_guest_cannot_access_authenticated_api_routes(): void
    {
        $this->getJson('/api/v1/auth/me')->assertUnauthorized();
    }
}
