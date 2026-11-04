<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_signed_link_verifies_the_authenticated_user_email(): void
    {
        $user = User::factory()->unverified()->create();
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(30),
            [
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ],
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Your email has been verified. You can now sign in.');

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_unsigned_link_does_not_verify_the_user_email(): void
    {
        $user = User::factory()->unverified()->create();
        $verificationUrl = route('verification.verify', [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ]);

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertForbidden();
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_unverified_user_can_request_a_new_verification_email(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)
            ->from(route('verification.notice'))
            ->post(route('verification.send'));

        $response->assertRedirect(route('verification.notice'))
            ->assertSessionHas('status', 'A new verification link has been sent to your email address.');

        Notification::assertSentTo($user, VerifyEmail::class);
    }
}
