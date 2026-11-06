<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_view_and_update_only_their_own_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Taylor Reed',
            'email' => 'taylor@example.com',
        ]);

        $this->actingAs($user)
            ->getJson('/api/v1/profile')
            ->assertOk()
            ->assertJsonPath('data.email', 'taylor@example.com')
            ->assertJsonPath('data.role', 'employee');

        $this->patchJson('/api/v1/profile', [
            'name' => 'Taylor Updated',
            'email' => 'taylor.updated@example.com',
            'is_admin' => true,
        ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Taylor Updated')
            ->assertJsonPath('data.role', 'employee');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Taylor Updated',
            'email' => 'taylor.updated@example.com',
            'is_admin' => false,
        ]);
    }

    public function test_admin_can_view_their_profile_role(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin)
            ->getJson('/api/v1/profile')
            ->assertOk()
            ->assertJsonPath('data.role', 'admin');
    }

    public function test_guest_cannot_view_a_profile(): void
    {
        $this->getJson('/api/v1/profile')->assertUnauthorized();
    }
}
