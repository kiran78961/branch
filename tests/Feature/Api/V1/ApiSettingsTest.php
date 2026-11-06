<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_read_and_update_their_attendance_preferences(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/v1/settings/attendance')
            ->assertOk()
            ->assertJsonPath('data.company_start_time', '10:00')
            ->assertJsonPath('data.daily_work_hours', 8);

        $this->patchJson('/api/v1/settings/attendance', [
            'lunch_start_time' => '12:30',
            'lunch_end_time' => '13:15',
            'daily_work_hours' => 7,
        ])
            ->assertOk()
            ->assertJsonPath('data.lunch_start_time', '12:30')
            ->assertJsonPath('data.daily_work_hours', 7);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'lunch_start_time' => '12:30',
            'lunch_end_time' => '13:15',
            'daily_work_hours' => 7,
        ]);
    }

    public function test_admin_can_update_company_attendance_start_time(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin)
            ->patchJson('/api/v1/admin/settings/attendance', [
                'workday_start_time' => '09:30',
            ])
            ->assertOk()
            ->assertJsonPath('data.workday_start_time', '09:30');

        $this->assertDatabaseHas('company_settings', [
            'id' => 1,
            'workday_start_time' => '09:30',
        ]);
    }

    public function test_employee_cannot_change_company_attendance_start_time(): void
    {
        $employee = User::factory()->create();

        $this->actingAs($employee)
            ->patchJson('/api/v1/admin/settings/attendance', [
                'workday_start_time' => '09:30',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('company_settings', [
            'id' => 1,
            'workday_start_time' => '10:00:00',
        ]);
    }

    public function test_employee_can_request_configured_holidays_for_a_year(): void
    {
        $employee = User::factory()->create();

        $this->actingAs($employee)
            ->getJson('/api/v1/holidays?year=2026')
            ->assertOk()
            ->assertJsonPath('data.year', 2026)
            ->assertJsonPath('data.holidays.0.name', 'Republic Day');
    }
}
