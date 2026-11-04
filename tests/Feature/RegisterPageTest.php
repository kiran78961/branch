<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegisterPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_page_renders_account_creation_fields(): void
    {
        $response = $this->get('/register');

        $response->assertOk()
            ->assertSee('Create your account')
            ->assertSee('method="POST"', false)
            ->assertSee('action="'.route('register.store').'"', false)
            ->assertSee('type="submit"', false)
            ->assertSee('First name')
            ->assertSee('Last name')
            ->assertSee('Work email')
            ->assertSee('Confirm password')
            ->assertSee('href="'.route('login').'"', false);
    }

    public function test_registration_stores_user_with_hashed_password(): void
    {
        Notification::fake();

        $response = $this->post(route('register.store'), [
            'first_name' => 'Alex',
            'last_name' => 'Morgan',
            'email' => 'alex@example.com',
            'password' => 'a-strong-password',
            'password_confirmation' => 'a-strong-password',
            'terms' => '1',
        ]);

        $response->assertRedirect(route('verification.notice'));

        $this->assertDatabaseHas('users', [
            'name' => 'Alex Morgan',
            'email' => 'alex@example.com',
            'email_verified_at' => null,
        ]);

        $user = User::query()->where('email', 'alex@example.com')->firstOrFail();
        $this->assertTrue(Hash::check('a-strong-password', $user->password));
        $this->assertAuthenticatedAs($user);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_registration_does_not_store_user_when_terms_are_not_accepted(): void
    {
        $response = $this->from(route('register'))->post(route('register.store'), [
            'first_name' => 'Alex',
            'last_name' => 'Morgan',
            'email' => 'alex@example.com',
            'password' => 'a-strong-password',
            'password_confirmation' => 'a-strong-password',
        ]);

        $response->assertRedirect(route('register'))
            ->assertSessionHasErrors('terms');

        $this->assertDatabaseMissing('users', [
            'email' => 'alex@example.com',
        ]);
    }
}
