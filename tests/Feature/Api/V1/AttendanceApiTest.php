<?php

namespace Tests\Feature\Api\V1;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_check_in_is_late_after_the_company_start_time(): void
    {
        $user = User::factory()->create();
        $this->travelTo('2026-11-06 04:30:01');

        $this->actingAs($user)
            ->postJson('/api/v1/attendance/check-in', ['work_location' => 'wfh'])
            ->assertCreated()
            ->assertJsonPath('data.work_location', 'wfh')
            ->assertJsonPath('data.is_late', true);

        $this->getJson('/api/v1/attendance/today')
            ->assertOk()
            ->assertJsonPath('data.day_status', 'late')
            ->assertJsonPath('data.company_start_time', '10:00')
            ->assertJsonPath('data.attendance.work_location', 'wfh');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_location' => 'wfh',
            'is_late' => true,
        ]);
    }

    public function test_employee_cannot_check_in_twice(): void
    {
        $user = User::factory()->create();
        Attendance::factory()->for($user)->create([
            'attendance_date' => today()->toDateString(),
        ]);

        $this->actingAs($user)
            ->postJson('/api/v1/attendance/check-in')
            ->assertStatus(409)
            ->assertJsonPath('message', 'You have already checked in today.');
    }

    public function test_employee_can_view_only_their_own_attendance_history(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        Attendance::factory()->for($user)->create([
            'attendance_date' => '2026-11-05',
        ]);
        Attendance::factory()->for($otherUser)->create([
            'attendance_date' => '2026-11-05',
        ]);

        $this->actingAs($user)
            ->getJson('/api/v1/attendance')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attendance_date', '2026-11-05')
            ->assertJsonStructure(['meta' => ['current_page', 'total']]);
    }

    public function test_employee_lunch_and_checkout_actions_update_attendance(): void
    {
        $user = User::factory()->create();
        $this->travelTo('2026-11-06 04:00:00');
        $attendance = Attendance::factory()->for($user)->create([
            'attendance_date' => today()->toDateString(),
            'check_in_at' => now(),
        ]);
        $this->travelTo('2026-11-06 07:00:00');

        $this->actingAs($user)
            ->postJson('/api/v1/attendance/lunch/start')
            ->assertOk()
            ->assertJsonPath('data.id', $attendance->id)
            ->assertJsonPath('data.lunch_started_at', '2026-11-06T07:00:00.000000Z');

        $this->travelTo('2026-11-06 07:30:00');

        $this->postJson('/api/v1/attendance/lunch/end')
            ->assertOk()
            ->assertJsonPath('data.lunch_ended_at', '2026-11-06T07:30:00.000000Z');

        $this->travelTo('2026-11-06 12:00:00');

        $this->postJson('/api/v1/attendance/check-out')
            ->assertOk()
            ->assertJsonPath('data.check_out_at', '2026-11-06T12:00:00.000000Z');

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'check_out_at' => '2026-11-06 12:00:00',
        ]);
    }

    public function test_check_in_rejects_an_invalid_work_location(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/v1/attendance/check-in', ['work_location' => 'remote'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('work_location');

        $this->assertDatabaseCount('attendances', 0);
    }
}
