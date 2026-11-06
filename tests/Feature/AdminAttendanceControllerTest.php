<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAttendanceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_cannot_access_admin_attendance_reports(): void
    {
        $employee = User::factory()->create();

        $this->actingAs($employee)
            ->get(route('admin.attendance.index'))
            ->assertForbidden();

        $this->actingAs($employee)
            ->get(route('admin.staff.create'))
            ->assertForbidden();

        $this->actingAs($employee)
            ->get(route('admin.staff.show', $employee))
            ->assertForbidden();
    }

    public function test_admin_can_view_attendance_for_a_selected_day(): void
    {
        $admin = User::factory()->administrator()->create();
        $employee = User::factory()->create();
        Attendance::factory()->for($employee)->create([
            'attendance_date' => '2026-11-04',
            'work_location' => 'wfh',
            'is_late' => true,
            'check_in_at' => '2026-11-04 09:00:00',
        ]);
        Attendance::factory()->for($employee)->create([
            'attendance_date' => '2026-11-05',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.attendance.index', ['date' => '2026-11-04']));

        $response->assertOk()
            ->assertSee('Work location')
            ->assertSee('WFH')
            ->assertSee('2:30 PM')
            ->assertSee('Employee arrived late')
            ->assertSee('>Late</span>', false)
            ->assertSee('Daily attendance')
            ->assertViewHas('summary', fn (array $summary): bool => $summary['late'] === 1)
            ->assertViewHas('reportDate', '2026-11-04')
            ->assertViewHas('reportType', 'daily')
            ->assertViewHas('reportRows', function ($reportRows): bool {
                $employee = $reportRows->getCollection()->first(
                    fn (User $user): bool => ! $user->is_admin,
                );

                return $employee !== null
                    && $employee->attendances->count() === 1
                    && $employee->attendances->first()->attendance_date->toDateString() === '2026-11-04';
            });
    }

    public function test_admin_weekly_report_covers_monday_through_sunday(): void
    {
        $admin = User::factory()->administrator()->create();
        $employee = User::factory()->create(['name' => 'Weekly employee']);
        Attendance::factory()->for($employee)->create([
            'attendance_date' => '2026-11-02',
            'work_location' => 'office',
        ]);
        Attendance::factory()->for($employee)->create([
            'attendance_date' => '2026-11-04',
            'work_location' => 'wfh',
            'is_late' => true,
        ]);
        Attendance::factory()->for($employee)->create([
            'attendance_date' => '2026-11-09',
            'work_location' => 'office',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.attendance.index', ['report' => 'weekly', 'date' => '2026-11-04']))
            ->assertOk()
            ->assertViewHas('rangeStart', '2026-11-02')
            ->assertViewHas('rangeEnd', '2026-11-08')
            ->assertViewHas('summary', fn (array $summary): bool => $summary['office'] === 1
                && $summary['wfh'] === 1
                && $summary['late'] === 1
                && $summary['absent'] === 8)
            ->assertViewHas('reportRows', function ($reportRows) use ($employee): bool {
                $weeklyEmployee = $reportRows->getCollection()->first(
                    fn (User $user): bool => $user->is($employee),
                );

                return $weeklyEmployee !== null
                    && $weeklyEmployee->report_office_days === 1
                    && $weeklyEmployee->report_wfh_days === 1
                    && $weeklyEmployee->report_late_days === 1
                    && $weeklyEmployee->report_absent_days === 3;
            });
    }

    public function test_admin_monthly_report_covers_the_calendar_month(): void
    {
        $admin = User::factory()->administrator()->create();
        $employee = User::factory()->create(['name' => 'Monthly employee']);
        Attendance::factory()->for($employee)->create([
            'attendance_date' => '2026-10-30',
        ]);
        Attendance::factory()->for($employee)->create([
            'attendance_date' => '2026-11-02',
            'is_late' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.attendance.index', ['report' => 'monthly', 'date' => '2026-11-06']))
            ->assertOk()
            ->assertSee('November 2026')
            ->assertViewHas('rangeStart', '2026-11-01')
            ->assertViewHas('rangeEnd', '2026-11-30')
            ->assertViewHas('summary', fn (array $summary): bool => $summary['office'] === 1
                && $summary['late'] === 1
                && $summary['absent'] === 39)
            ->assertViewHas('reportRows', function ($reportRows) use ($employee): bool {
                $monthlyEmployee = $reportRows->getCollection()->first(
                    fn (User $user): bool => $user->is($employee),
                );

                return $monthlyEmployee !== null
                    && $monthlyEmployee->report_office_days === 1
                    && $monthlyEmployee->report_late_days === 1
                    && $monthlyEmployee->report_absent_days === 19;
            });
    }

    public function test_admin_absent_report_excludes_approved_leave_weekends_and_holidays(): void
    {
        $admin = User::factory()->administrator()->create();
        $absentEmployee = User::factory()->create(['name' => 'Absent employee']);
        $leaveEmployee = User::factory()->create(['name' => 'Leave employee']);
        $leaveRequest = $leaveEmployee->leaveRequests()->create([
            'leave_type' => 'annual',
            'start_date' => '2026-11-06',
            'end_date' => '2026-11-06',
            'reason' => 'Approved leave',
        ]);
        $leaveRequest->status = 'approved';
        $leaveRequest->save();

        $this->actingAs($admin)
            ->get(route('admin.attendance.index', ['report' => 'absent', 'date' => '2026-11-06']))
            ->assertOk()
            ->assertSee('Absent employees')
            ->assertViewHas('rangeStart', '2026-11-01')
            ->assertViewHas('rangeEnd', '2026-11-30')
            ->assertViewHas('reportRows', function ($reportRows) use ($absentEmployee, $leaveEmployee): bool {
                $rows = $reportRows->getCollection();
                $absentEmployeeWasReportedOnWorkingDay = $rows->contains(
                    fn (array $row): bool => $row['staffMember']->is($absentEmployee)
                        && $row['date'] === '2026-11-06',
                );
                $leaveEmployeeWasReportedOnApprovedLeave = $rows->contains(
                    fn (array $row): bool => $row['staffMember']->is($leaveEmployee)
                        && $row['date'] === '2026-11-06',
                );
                $nonWorkingDateWasReported = $rows->contains(
                    fn (array $row): bool => in_array($row['date'], ['2026-11-08', '2026-11-24'], true),
                );

                return $absentEmployeeWasReportedOnWorkingDay
                    && ! $leaveEmployeeWasReportedOnApprovedLeave
                    && ! $nonWorkingDateWasReported;
            });
    }

    public function test_admin_attendance_report_rejects_an_unknown_report_type(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin)
            ->from(route('admin.attendance.index'))
            ->get(route('admin.attendance.index', ['report' => 'yearly']))
            ->assertRedirect(route('admin.attendance.index'))
            ->assertSessionHasErrors('report');
    }

    public function test_unverified_accounts_are_included_in_staff_attendance_reports(): void
    {
        $admin = User::factory()->administrator()->create();
        $employee = User::factory()->create();
        User::factory()->unverified()->create();

        $this->actingAs($admin)
            ->get(route('admin.attendance.index'))
            ->assertOk()
            ->assertViewHas('staffCount', 3)
            ->assertViewHas('reportRows', function ($reportRows) use ($employee): bool {
                return $reportRows->getCollection()->pluck('id')->contains($employee->id)
                    && $reportRows->getCollection()->count() === 3;
            });
    }

    public function test_admin_can_promote_an_employee_but_cannot_demote_themselves(): void
    {
        $admin = User::factory()->administrator()->create();
        $employee = User::factory()->create();

        $this->actingAs($admin)
            ->patch(route('admin.staff.role', $employee), ['is_admin' => true])
            ->assertRedirect(route('admin.staff.index'));

        $this->assertDatabaseHas('users', [
            'id' => $employee->id,
            'is_admin' => true,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.staff.index'))
            ->patch(route('admin.staff.role', $admin), ['is_admin' => false])
            ->assertRedirect(route('admin.staff.index'))
            ->assertSessionHasErrors('is_admin');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'is_admin' => true,
        ]);
    }

    public function test_admin_staff_page_escapes_employee_names(): void
    {
        $admin = User::factory()->administrator()->create();
        User::factory()->create(['name' => '<script>alert(1)</script>']);

        $this->actingAs($admin)
            ->get(route('admin.staff.index'))
            ->assertOk()
            ->assertSee('<aside', false)
            ->assertSee('Administration')
            ->assertSee('Attendance history')
            ->assertDontSee('Sign out')
            ->assertDontSee('data-theme-toggle', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_admin_can_create_an_employee_with_a_temporary_password(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin)
            ->get(route('admin.staff.create'))
            ->assertOk()
            ->assertSee('Create an employee');

        $response = $this->actingAs($admin)->post(route('admin.staff.store'), [
            'name' => 'Jamie Employee',
            'email' => 'jamie@example.com',
            'password' => 'temporary-password',
            'password_confirmation' => 'temporary-password',
            'is_admin' => true,
        ]);

        $employee = User::query()->where('email', 'jamie@example.com')->firstOrFail();

        $response->assertRedirect(route('admin.staff.show', $employee));
        $this->assertDatabaseHas('users', [
            'id' => $employee->id,
            'name' => 'Jamie Employee',
            'email' => 'jamie@example.com',
            'is_admin' => false,
        ]);
        $this->assertTrue(Hash::check('temporary-password', $employee->password));
    }

    public function test_admin_can_view_an_employees_profile_and_attendance_history(): void
    {
        $admin = User::factory()->administrator()->create();
        $employee = User::factory()->create(['name' => 'Taylor Worker']);
        Attendance::factory()->for($employee)->create([
            'attendance_date' => '2026-11-04',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.staff.show', $employee))
            ->assertOk()
            ->assertSee('Taylor Worker')
            ->assertSee('Nov 4, 2026')
            ->assertViewHas('attendances', fn ($attendances): bool => $attendances->total() === 1);
    }

    public function test_admin_cannot_create_an_employee_with_invalid_credentials(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin)
            ->from(route('admin.staff.create'))
            ->post(route('admin.staff.store'), [
                'name' => 'Jamie Employee',
                'email' => 'jamie@example.com',
                'password' => 'short',
                'password_confirmation' => 'different',
            ])
            ->assertRedirect(route('admin.staff.create'))
            ->assertSessionHasErrors(['password']);

        $this->assertDatabaseMissing('users', [
            'email' => 'jamie@example.com',
        ]);
    }

    public function test_invalid_report_date_is_rejected(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin)
            ->get(route('admin.attendance.index', ['date' => 'not-a-date']))
            ->assertSessionHasErrors('date');
    }
}
