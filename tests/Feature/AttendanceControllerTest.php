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
        $this->assertSame('office', $attendance->work_location);

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

    public function test_check_in_at_the_company_start_time_is_not_late(): void
    {
        $user = User::factory()->create();
        $this->travelTo('2026-11-06 04:30:00');

        $this->actingAs($user)
            ->post(route('attendance.check-in'))
            ->assertRedirect(route('attendance.index'));

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'is_late' => false,
        ]);

        $this->get(route('attendance.index'))
            ->assertOk()
            ->assertSee('In progress')
            ->assertDontSee('Late — Employee arrived late');
    }

    public function test_check_in_after_the_company_start_time_is_marked_late(): void
    {
        $user = User::factory()->create();
        $this->travelTo('2026-11-06 04:30:01');

        $this->actingAs($user)
            ->post(route('attendance.check-in'))
            ->assertRedirect(route('attendance.index'));

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'is_late' => true,
        ]);

        $this->get(route('attendance.index'))
            ->assertOk()
            ->assertSee('Late — Employee arrived late');
    }

    public function test_check_in_uses_the_start_time_saved_by_an_admin(): void
    {
        $admin = User::factory()->administrator()->create();
        $employee = User::factory()->create();

        $this->actingAs($admin)
            ->patch(route('settings.company-attendance.update'), [
                'workday_start_time' => '09:00',
            ])
            ->assertRedirect(route('settings.edit'));

        $this->travelTo('2026-11-06 03:30:01');

        $this->actingAs($employee)
            ->post(route('attendance.check-in'))
            ->assertRedirect(route('attendance.index'));

        $this->assertDatabaseHas('attendances', [
            'user_id' => $employee->id,
            'is_late' => true,
        ]);
    }

    public function test_employee_can_take_one_lunch_break_and_work_timer_excludes_it(): void
    {
        $user = User::factory()->create();
        $this->travelTo('2026-11-06 09:00:00');
        $attendance = Attendance::factory()->for($user)->create([
            'attendance_date' => '2026-11-06',
            'check_in_at' => now(),
        ]);

        $this->travelTo('2026-11-06 13:00:00');

        $this->actingAs($user)
            ->post(route('attendance.lunch.start'))
            ->assertRedirect(route('attendance.index'));

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'lunch_started_at' => '2026-11-06 13:00:00',
            'lunch_ended_at' => null,
        ]);

        $this->get(route('attendance.index'))
            ->assertOk()
            ->assertSee('End lunch')
            ->assertSee('Lunch break');

        $this->travelTo('2026-11-06 13:30:00');

        $this->actingAs($user)
            ->from(route('attendance.index'))
            ->post(route('attendance.check-out'))
            ->assertRedirect(route('attendance.index'))
            ->assertSessionHasErrors('attendance');

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'check_out_at' => null,
        ]);

        $this->travelTo('2026-11-06 14:00:00');

        $this->actingAs($user)
            ->post(route('attendance.lunch.end'))
            ->assertRedirect(route('attendance.index'));

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'lunch_ended_at' => '2026-11-06 14:00:00',
        ]);

        $this->travelTo('2026-11-06 17:00:00');

        $this->actingAs($user)
            ->post(route('attendance.check-out'))
            ->assertRedirect(route('attendance.index'));

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'check_out_at' => '2026-11-06 17:00:00',
        ]);

        $this->get(route('attendance.index'))
            ->assertOk()
            ->assertSee('Lunch break')
            ->assertSee('Planned window: 1:00 PM–2:00 PM')
            ->assertSee('data-lunch-timer', false)
            ->assertSee(
                'data-lunch-started-at="'.$attendance->fresh()->lunch_started_at->toIso8601String().'"',
                false,
            )
            ->assertSee(
                'data-lunch-ended-at="'.$attendance->fresh()->lunch_ended_at->toIso8601String().'"',
                false,
            );
    }

    public function test_employee_cannot_take_a_second_lunch_break(): void
    {
        $user = User::factory()->create();
        $this->travelTo('2026-11-06 09:00:00');
        $attendance = Attendance::factory()->for($user)->create([
            'attendance_date' => '2026-11-06',
            'check_in_at' => now(),
            'lunch_started_at' => now()->setTime(13, 0),
            'lunch_ended_at' => now()->setTime(14, 0),
        ]);
        $this->travelTo('2026-11-06 15:00:00');

        $this->actingAs($user)
            ->from(route('attendance.index'))
            ->post(route('attendance.lunch.start'))
            ->assertRedirect(route('attendance.index'))
            ->assertSessionHasErrors('attendance');

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'lunch_started_at' => '2026-11-06 13:00:00',
            'lunch_ended_at' => '2026-11-06 14:00:00',
        ]);
    }

    public function test_employee_cannot_end_lunch_before_starting_it(): void
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->for($user)->create([
            'attendance_date' => today()->toDateString(),
        ]);

        $this->actingAs($user)
            ->from(route('attendance.index'))
            ->post(route('attendance.lunch.end'))
            ->assertRedirect(route('attendance.index'))
            ->assertSessionHasErrors('attendance');

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'lunch_started_at' => null,
            'lunch_ended_at' => null,
        ]);
    }

    public function test_employee_cannot_start_lunch_twice_while_break_is_active(): void
    {
        $user = User::factory()->create();
        $this->travelTo('2026-11-06 13:15:00');
        $attendance = Attendance::factory()->for($user)->create([
            'attendance_date' => '2026-11-06',
            'check_in_at' => now()->setTime(9, 0),
            'lunch_started_at' => now()->setTime(13, 0),
        ]);

        $this->actingAs($user)
            ->from(route('attendance.index'))
            ->post(route('attendance.lunch.start'))
            ->assertRedirect(route('attendance.index'))
            ->assertSessionHasErrors('attendance');

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'lunch_started_at' => '2026-11-06 13:00:00',
            'lunch_ended_at' => null,
        ]);
    }

    public function test_employee_cannot_start_lunch_before_checking_in(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('attendance.index'))
            ->post(route('attendance.lunch.start'))
            ->assertRedirect(route('attendance.index'))
            ->assertSessionHasErrors('attendance');

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_guest_is_redirected_to_sign_in_before_viewing_attendance(): void
    {
        $this->get(route('attendance.index'))
            ->assertRedirect(route('login'));

        $this->get(route('attendance.history'))
            ->assertRedirect(route('login'));

        $this->post(route('attendance.lunch.start'))
            ->assertRedirect(route('login'));

        $this->post(route('attendance.lunch.end'))
            ->assertRedirect(route('login'));
    }

    public function test_employee_can_view_attendance_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('attendance.index'))
            ->assertOk()
            ->assertDontSee('Sign out')
            ->assertDontSee('data-theme-toggle', false);
    }

    public function test_attendance_page_shows_local_check_times_and_work_timer(): void
    {
        $user = User::factory()->create();
        $this->travelTo('2026-11-06 10:00:00');
        $attendance = Attendance::factory()->for($user)->create([
            'attendance_date' => '2026-11-06',
            'check_in_at' => now()->subHour(),
            'check_out_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('attendance.index'))
            ->assertOk()
            ->assertSee('2:30 PM')
            ->assertSee('3:30 PM')
            ->assertSee('Present')
            ->assertSee('Working remote')
            ->assertSee('Approved leaves')
            ->assertSee('Unplanned')
            ->assertSee('data-attendance-summary-value="present">1', false)
            ->assertSee('data-attendance-summary-value="wfh">0', false)
            ->assertSee('data-attendance-summary-value="leave">0', false)
            ->assertSee('data-attendance-summary-value="absent">0', false)
            ->assertSee('Time worked')
            ->assertSee('Daily working-hours target')
            ->assertSee('data-work-target-seconds="28800"', false)
            ->assertSee('0% of 8-hour target')
            ->assertDontSee('data-current-time', false)
            ->assertDontSee('India time (IST)')
            ->assertSee('data-work-timer', false)
            ->assertSee(
                'data-check-in-at="'.$attendance->check_in_at->toIso8601String().'"',
                false,
            );
    }

    public function test_employee_can_check_in_from_home_and_status_summary_shows_wfh(): void
    {
        $user = User::factory()->create();
        $this->travelTo('2026-11-06 09:00:00');

        $this->actingAs($user)
            ->post(route('attendance.check-in'), ['work_location' => 'wfh'])
            ->assertRedirect(route('attendance.index'));

        $attendance = Attendance::query()->whereBelongsTo($user)->firstOrFail();
        $this->assertSame('wfh', $attendance->work_location);

        $this->get(route('attendance.index'))
            ->assertOk()
            ->assertSee('data-attendance-summary-value="present">0', false)
            ->assertSee('data-attendance-summary-value="wfh">1', false)
            ->assertSee('Working remotely');
    }

    public function test_employee_cannot_check_in_with_an_invalid_work_location(): void
    {
        $user = User::factory()->create();
        $this->travelTo('2026-11-06 09:00:00');

        $this->actingAs($user)
            ->from(route('attendance.index'))
            ->post(route('attendance.check-in'), ['work_location' => 'somewhere-else'])
            ->assertRedirect(route('attendance.index'))
            ->assertSessionHasErrors('work_location');

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_status_summary_counts_approved_leave_instead_of_absence(): void
    {
        $user = User::factory()->create();
        $this->travelTo('2026-11-06 09:00:00');
        $leaveRequest = $user->leaveRequests()->create([
            'leave_type' => 'annual',
            'start_date' => '2026-11-05',
            'end_date' => '2026-11-06',
            'reason' => 'Approved day off',
        ]);
        $leaveRequest->status = 'approved';
        $leaveRequest->save();

        $this->actingAs($user)
            ->get(route('attendance.index'))
            ->assertOk()
            ->assertSee('data-attendance-summary-value="leave">1', false)
            ->assertSee('data-attendance-summary-value="absent">0', false);
    }

    public function test_status_summary_does_not_count_weekends_or_holidays_as_absent(): void
    {
        $user = User::factory()->create();
        $this->travelTo('2026-11-08 09:00:00');

        $this->actingAs($user)
            ->get(route('attendance.index'))
            ->assertOk()
            ->assertSee('data-attendance-summary-value="absent">0', false);

        $this->travelTo('2026-11-09 09:00:00');

        $this->get(route('attendance.index'))
            ->assertOk()
            ->assertSee('data-attendance-summary-value="absent">1', false);
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
