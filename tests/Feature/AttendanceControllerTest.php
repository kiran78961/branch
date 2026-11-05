<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_check_in_and_check_out_once_a_day(): void
    {
        $user = User::factory()->create();
        $this->travelTo('2026-11-05 09:00:00');

        $this->actingAs($user)
            ->post(route('attendance.check-in'))
            ->assertRedirect(route('attendance.index'));

        $attendance = Attendance::query()->where('user_id', $user->id)->firstOrFail();
        $this->assertSame('2026-11-05', $attendance->attendance_date->toDateString());
        $this->assertSame('2026-11-05 09:00:00', $attendance->check_in_at->format('Y-m-d H:i:s'));

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'check_out_at' => null,
        ]);

        $this->travelTo('2026-11-05 17:00:00');

        $this->actingAs($user)
            ->post(route('attendance.check-out'))
            ->assertRedirect(route('attendance.index'));

        $this->assertSame(
            '2026-11-05 17:00:00',
            $attendance->fresh()->check_out_at->format('Y-m-d H:i:s'),
        );

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'check_out_at' => '2026-11-05 17:00:00',
        ]);

        $this->travelTo('2026-11-05 17:15:00');

        $this->actingAs($user)
            ->from(route('attendance.index'))
            ->post(route('attendance.check-out'))
            ->assertRedirect(route('attendance.index'))
            ->assertSessionHasErrors('attendance');

        $this->assertSame(
            '2026-11-05 17:00:00',
            $attendance->fresh()->check_out_at->format('Y-m-d H:i:s'),
        );
    }

    public function test_guest_is_redirected_to_sign_in_before_viewing_attendance(): void
    {
        $this->get(route('attendance.index'))
            ->assertRedirect(route('login'));

        $this->get(route('attendance.history'))
            ->assertRedirect(route('login'));
    }

    public function test_unverified_employee_can_view_attendance_without_verification(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get(route('attendance.index'))
            ->assertOk()
            ->assertSee('data-theme-toggle', false);
    }

    public function test_employee_cannot_check_in_twice_on_the_same_day(): void
    {
        $user = User::factory()->create();
        $this->travelTo('2026-11-05 09:00:00');
        $this->actingAs($user)->post(route('attendance.check-in'));

        $this->travelTo('2026-11-05 09:15:00');

        $this->actingAs($user)
            ->from(route('attendance.index'))
            ->post(route('attendance.check-in'))
            ->assertRedirect(route('attendance.index'))
            ->assertSessionHasErrors('attendance');

        $this->assertDatabaseCount('attendances', 1);
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'check_in_at' => '2026-11-05 09:00:00',
        ]);
    }

    public function test_employee_cannot_check_out_before_checking_in(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('attendance.index'))
            ->post(route('attendance.check-out'))
            ->assertRedirect(route('attendance.index'))
            ->assertSessionHasErrors('attendance');

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_employee_can_open_their_own_attendance_history(): void
    {
        $employee = User::factory()->create();
        $otherEmployee = User::factory()->create();
        Attendance::factory()->for($employee)->create([
            'attendance_date' => '2026-11-04',
        ]);
        Attendance::factory()->for($otherEmployee)->create([
            'attendance_date' => '2026-11-04',
        ]);

        $response = $this->actingAs($employee)->get(route('attendance.history'));

        $response->assertOk()
            ->assertSee('<aside', false)
            ->assertSee('branch-wordmark', false)
            ->assertSee('href="'.route('attendance.history').'"', false)
            ->assertSee('Attendance history')
            ->assertSee('All attendance records')
            ->assertViewHas('attendanceRecords', function ($attendanceRecords) use ($employee): bool {
                return $attendanceRecords->total() === 1
                    && $attendanceRecords->getCollection()->every(
                        fn (Attendance $attendance): bool => $attendance->user_id === $employee->id,
                    );
            });
    }

    public function test_employee_can_filter_their_attendance_history_by_calendar_date(): void
    {
        $employee = User::factory()->create();
        $otherEmployee = User::factory()->create();
        Attendance::factory()->for($employee)->create([
            'attendance_date' => '2026-11-04',
        ]);
        Attendance::factory()->for($employee)->create([
            'attendance_date' => '2026-11-05',
        ]);
        Attendance::factory()->for($otherEmployee)->create([
            'attendance_date' => '2026-11-04',
        ]);

        $this->actingAs($employee)
            ->get(route('attendance.history', ['date' => '2026-11-04']))
            ->assertOk()
            ->assertSee('November 4, 2026')
            ->assertViewHas('attendanceRecords', function ($attendanceRecords) use ($employee): bool {
                return $attendanceRecords->total() === 1
                    && $attendanceRecords->getCollection()->every(
                        fn (Attendance $attendance): bool => $attendance->user_id === $employee->id
                            && $attendance->attendance_date->toDateString() === '2026-11-04',
                    );
            });
    }

    public function test_employee_cannot_filter_attendance_history_with_an_invalid_date(): void
    {
        $employee = User::factory()->create();

        $this->followingRedirects()
            ->actingAs($employee)
            ->from(route('attendance.history'))
            ->get(route('attendance.history', ['date' => 'not-a-date']))
            ->assertOk()
            ->assertSee('Choose a valid calendar date.');
    }
}
