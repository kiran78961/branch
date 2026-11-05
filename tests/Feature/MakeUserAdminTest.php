<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MakeUserAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_promotes_a_registered_employee_to_admin(): void
    {
        $user = User::factory()->create();

        $this->artisan('attendance:make-admin', ['email' => $user->email])
            ->expectsOutputToContain('is now an administrator')
            ->assertSuccessful();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_admin' => true,
        ]);
    }

    public function test_command_fails_when_the_email_is_not_registered(): void
    {
        $this->artisan('attendance:make-admin', ['email' => 'missing@example.com'])
            ->expectsOutputToContain('No user was found')
            ->assertFailed();
    }
}
