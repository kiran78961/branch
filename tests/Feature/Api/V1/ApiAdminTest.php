<?php

namespace Tests\Feature\Api\V1;

use App\Models\Attendance;
use App\Models\User;
use App\Notifications\SalaryChangedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_is_forbidden_from_admin_api_routes(): void
    {
        $employee = User::factory()->create();

        $this->actingAs($employee)
            ->getJson('/api/v1/admin/staff')
            ->assertForbidden();
    }

    public function test_admin_can_create_staff_and_update_their_role(): void
    {
        $admin = User::factory()->administrator()->create();

        $created = $this->actingAs($admin)
            ->postJson('/api/v1/admin/staff', [
                'name' => 'New employee',
                'email' => 'new.employee@example.com',
                'password' => 'secret-password',
                'password_confirmation' => 'secret-password',
                'is_admin' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('data.role', 'employee');
        $employeeId = $created->json('data.id');

        $this->patchJson("/api/v1/admin/staff/{$employeeId}/role", ['is_admin' => true])
            ->assertOk()
            ->assertJsonPath('data.role', 'admin');

        $this->assertDatabaseHas('users', [
            'id' => $employeeId,
            'is_admin' => true,
        ]);
    }

    public function test_admin_can_assign_an_employees_monthly_salary(): void
    {
        $admin = User::factory()->administrator()->create();
        $employee = User::factory()->create();

        $this->actingAs($admin)
            ->patchJson("/api/v1/admin/staff/{$employee->id}/salary", [
                'monthly_salary' => '68000.50',
            ])
            ->assertOk()
            ->assertJsonPath('data.id', $employee->id)
            ->assertJsonPath('data.monthly_salary', '68000.50');

        $this->assertDatabaseHas('users', [
            'id' => $employee->id,
            'monthly_salary' => '68000.50',
        ]);
        $this->assertDatabaseHas('salary_histories', [
            'user_id' => $employee->id,
            'changed_by' => $admin->id,
            'monthly_salary' => '68000.50',
            'change_type' => 'assigned',
        ]);

        $notification = $employee->notifications()->firstOrFail();
        $this->assertSame(SalaryChangedNotification::class, $notification->type);
        $this->assertArrayNotHasKey('monthly_salary', $notification->data);
    }

    public function test_monthly_salary_assignment_requires_authentication(): void
    {
        $employee = User::factory()->create();

        $this->patchJson("/api/v1/admin/staff/{$employee->id}/salary", [
            'monthly_salary' => '68000.50',
        ])->assertUnauthorized();
    }

    public function test_employee_is_forbidden_from_assigning_an_employees_monthly_salary(): void
    {
        $employee = User::factory()->create();
        $coworker = User::factory()->create([
            'monthly_salary' => '50000.00',
        ]);

        $this->actingAs($employee)
            ->patchJson("/api/v1/admin/staff/{$coworker->id}/salary", [
                'monthly_salary' => '1.00',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('users', [
            'id' => $coworker->id,
            'monthly_salary' => '50000.00',
        ]);
    }

    public function test_admin_cannot_assign_a_negative_monthly_salary(): void
    {
        $admin = User::factory()->administrator()->create();
        $employee = User::factory()->create([
            'monthly_salary' => '50000.00',
        ]);

        $this->actingAs($admin)
            ->patchJson("/api/v1/admin/staff/{$employee->id}/salary", [
                'monthly_salary' => '-1.00',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('monthly_salary');

        $this->assertDatabaseHas('users', [
            'id' => $employee->id,
            'monthly_salary' => '50000.00',
        ]);
    }

    public function test_admin_attendance_report_returns_summary_and_late_counts(): void
    {
        $admin = User::factory()->administrator()->create();
        $employee = User::factory()->create();
        Attendance::factory()->for($employee)->create([
            'attendance_date' => '2026-11-06',
            'is_late' => true,
        ]);

        $this->actingAs($admin)
            ->getJson('/api/v1/admin/reports/attendance?date=2026-11-06')
            ->assertOk()
            ->assertJsonPath('meta.report', 'daily')
            ->assertJsonPath('meta.range_start', '2026-11-06')
            ->assertJsonPath('meta.summary.office', 1)
            ->assertJsonPath('meta.summary.late', 1);
    }

    public function test_admin_cannot_demote_themselves(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin)
            ->patchJson("/api/v1/admin/staff/{$admin->id}/role", ['is_admin' => false])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'You cannot remove your own administrator access.');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'is_admin' => true,
        ]);
    }

    public function test_admin_can_approve_a_pending_leave_request(): void
    {
        $admin = User::factory()->administrator()->create();
        $employee = User::factory()->create();
        $leaveRequest = $employee->leaveRequests()->create([
            'leave_type' => 'annual',
            'start_date' => '2026-11-09',
            'end_date' => '2026-11-09',
            'reason' => 'Family event',
        ]);

        $this->actingAs($admin)
            ->patchJson("/api/v1/admin/leave-requests/{$leaveRequest->id}", [
                'status' => 'approved',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonPath('data.employee.id', $employee->id)
            ->assertJsonPath('data.reviewer.id', $admin->id);

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'approved',
            'reviewed_by' => $admin->id,
        ]);
    }
}
