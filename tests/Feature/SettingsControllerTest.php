<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_open_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('settings.edit'))
            ->assertOk()
            ->assertSee('Settings')
            ->assertDontSee('profile-settings-heading', false)
            ->assertDontSee('password-settings-heading', false)
            ->assertSee('Appearance')
            ->assertSee('Attendance preferences')
            ->assertSee('Daily working-hours target')
            ->assertSee('value="8"', false)
            ->assertSee('action="'.route('logout').'"', false)
            ->assertSee('Sign out')
            ->assertSee('href="'.route('settings.edit').'"', false)
            ->assertSee('href="'.route('profile.show').'"', false)
            ->assertSee('data-theme-toggle', false)
            ->assertSee('value="13:00"', false)
            ->assertSee('value="14:00"', false)
            ->assertSee('Company workday starts at 10:00 AM')
            ->assertDontSee('Save profile')
            ->assertDontSee(route('settings.company-attendance.update'));
    }

    public function test_employee_can_view_and_update_their_profile_page(): void
    {
        $user = User::factory()->create([
            'name' => 'Taylor Reed',
            'email' => 'taylor@example.com',
        ]);

        $this->actingAs($user)
            ->get(route('profile.show'))
            ->assertOk()
            ->assertSee('My Profile')
            ->assertSee('Employee')
            ->assertSee('value="Taylor Reed"', false)
            ->assertSee('value="taylor@example.com"', false)
            ->assertSee('action="'.route('profile.update').'"', false);
    }

    public function test_admin_can_view_their_profile_page(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin)
            ->get(route('profile.show'))
            ->assertOk()
            ->assertSee('My Profile')
            ->assertSee('Administrator')
            ->assertSee('href="'.route('profile.show').'"', false);
    }

    public function test_admin_can_update_the_shared_company_start_time(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin)
            ->get(route('settings.edit'))
            ->assertOk()
            ->assertSee('value="10:00"', false)
            ->assertSee(route('settings.company-attendance.update'));

        $this->patch(route('settings.company-attendance.update'), [
            'workday_start_time' => '09:30',
        ])
            ->assertRedirect(route('settings.edit'))
            ->assertSessionHas('status', 'The company start time has been updated.');

        $this->assertDatabaseHas('company_settings', [
            'id' => 1,
            'workday_start_time' => '09:30',
        ]);
    }

    public function test_employee_cannot_update_the_shared_company_start_time(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('settings.company-attendance.update'), [
                'workday_start_time' => '09:30',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('company_settings', [
            'id' => 1,
            'workday_start_time' => '10:00:00',
        ]);
    }

    public function test_company_start_time_must_be_a_valid_time(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin)
            ->from(route('settings.edit'))
            ->patch(route('settings.company-attendance.update'), [
                'workday_start_time' => '25:61',
            ])
            ->assertRedirect(route('settings.edit'))
            ->assertSessionHasErrors('workday_start_time');

        $this->assertDatabaseHas('company_settings', [
            'id' => 1,
            'workday_start_time' => '10:00:00',
        ]);
    }

    public function test_guest_is_redirected_to_sign_in_from_settings(): void
    {
        $this->get(route('settings.edit'))
            ->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_to_sign_in_from_profile(): void
    {
        $this->get(route('profile.show'))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_update_their_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Taylor Reed',
                'email' => 'taylor@example.com',
                'is_admin' => true,
            ])
            ->assertRedirect(route('profile.show'))
            ->assertSessionHas('status', 'Your profile has been updated.');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Taylor Reed',
            'email' => 'taylor@example.com',
            'is_admin' => false,
        ]);
    }

    public function test_user_cannot_change_profile_to_an_existing_email(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->actingAs($user)
            ->from(route('profile.show'))
            ->patch(route('profile.update'), [
                'name' => 'Updated Name',
                'email' => $otherUser->email,
            ])
            ->assertRedirect(route('profile.show'))
            ->assertSessionHasErrors('email');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }

    public function test_user_can_update_lunch_schedule_and_attendance_page_uses_it(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('settings.attendance.update'), [
                'lunch_start_time' => '12:30',
                'lunch_end_time' => '13:15',
                'daily_work_hours' => '7',
            ])
            ->assertRedirect(route('settings.edit'))
            ->assertSessionHas('status', 'Your attendance preferences have been updated.');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'lunch_start_time' => '12:30',
            'lunch_end_time' => '13:15',
            'daily_work_hours' => 7,
        ]);

        $this->get(route('attendance.index'))
            ->assertOk()
            ->assertSee('Planned window: 12:30 PM–1:15 PM')
            ->assertSee('data-work-target-seconds="25200"', false)
            ->assertSee('0% of 7-hour target');
    }

    public function test_lunch_schedule_end_must_be_after_start(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('settings.edit'))
            ->patch(route('settings.attendance.update'), [
                'lunch_start_time' => '14:00',
                'lunch_end_time' => '13:00',
                'daily_work_hours' => '8',
            ])
            ->assertRedirect(route('settings.edit'))
            ->assertSessionHasErrors('lunch_end_time');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'lunch_start_time' => '13:00:00',
            'lunch_end_time' => '14:00:00',
            'daily_work_hours' => 8,
        ]);
    }

    public function test_daily_work_hours_must_be_between_one_and_twenty_four(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('settings.edit'))
            ->patch(route('settings.attendance.update'), [
                'lunch_start_time' => '13:00',
                'lunch_end_time' => '14:00',
                'daily_work_hours' => '25',
            ])
            ->assertRedirect(route('settings.edit'))
            ->assertSessionHasErrors('daily_work_hours');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'daily_work_hours' => 8,
        ]);
    }
}
