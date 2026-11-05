<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_sign_in_with_valid_credentials(): void
    {
        $user = User::factory()->create();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('attendance.index'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_credentials_do_not_sign_in(): void
    {
        $user = User::factory()->create();

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'wrong-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_signed_in_employee_is_redirected_away_from_login_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('login'))
            ->assertRedirect(route('attendance.index'));
    }

    public function test_employee_can_sign_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
