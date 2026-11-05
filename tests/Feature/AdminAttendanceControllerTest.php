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
        ]);
        Attendance::factory()->for($employee)->create([
            'attendance_date' => '2026-11-05',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.attendance.index', ['date' => '2026-11-04']));

        $response->assertOk()
            ->assertViewHas('reportDate', '2026-11-04')
            ->assertViewHas('staff', function ($staff): bool {
                $employee = $staff->getCollection()->first(
                    fn (User $user): bool => ! $user->is_admin,
                );

                return $employee !== null
                    && $employee->attendances->count() === 1
                    && $employee->attendances->first()->attendance_date->toDateString() === '2026-11-04';
            });
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
            ->assertViewHas('staff', function ($staff) use ($employee): bool {
                return $staff->getCollection()->pluck('id')->contains($employee->id)
                    && $staff->getCollection()->count() === 3;
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
            ->assertSee('data-theme-toggle', false)
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
