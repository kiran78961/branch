<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\SalaryChangedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalarySlipControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_view_and_download_their_current_salary_slip_as_a_pdf(): void
    {
        $employee = User::factory()->create([
            'monthly_salary' => '72500.50',
        ]);
        $this->travelTo('2026-11-07 10:00:00');

        $this->actingAs($employee)
            ->get(route('salary-slip.show'))
            ->assertOk()
            ->assertSee('November 2026')
            ->assertSee('₹72,500.50')
            ->assertSee('Download PDF');

        $response = $this->get(route('salary-slip.download'));

        $response->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'attachment; filename=salary-slip-2026-11.pdf');

        $this->assertSame('%PDF-', substr($response->getContent(), 0, 5));
    }

    public function test_salary_slip_requires_authentication(): void
    {
        $this->get(route('salary-slip.show'))
            ->assertRedirect(route('login'));

        $this->get(route('salary-slip.download'))
            ->assertRedirect(route('login'));
    }

    public function test_employee_without_a_configured_salary_cannot_download_a_slip(): void
    {
        $employee = User::factory()->create();

        $this->actingAs($employee)
            ->get(route('salary-slip.show'))
            ->assertOk()
            ->assertSee('Your monthly salary has not been set yet.')
            ->assertDontSee('Download PDF');

        $this->get(route('salary-slip.download'))
            ->assertNotFound();
    }

    public function test_admin_can_update_an_employees_monthly_salary(): void
    {
        $admin = User::factory()->administrator()->create();
        $employee = User::factory()->create();

        $this->actingAs($admin)
            ->patch(route('admin.staff.salary', $employee), [
                'monthly_salary' => '68000.50',
            ])
            ->assertRedirect(route('admin.staff.show', $employee));

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
        $this->assertSame(
            'Your monthly salary has been updated. Visit your salary slip to review the details.',
            $notification->data['message'],
        );
        $this->assertArrayNotHasKey('monthly_salary', $notification->data);
    }

    public function test_employee_can_dismiss_their_salary_notification_but_not_another_employees(): void
    {
        $admin = User::factory()->administrator()->create();
        $employee = User::factory()->create();
        $coworker = User::factory()->create();

        $this->actingAs($admin)
            ->patch(route('admin.staff.salary', $employee), [
                'monthly_salary' => '68000.50',
            ]);

        $notification = $employee->notifications()->firstOrFail();

        $this->actingAs($coworker)
            ->post(route('salary-notifications.read', $notification))
            ->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);

        $this->actingAs($employee)
            ->get(route('salary-slip.show'))
            ->assertOk()
            ->assertSee('Your monthly salary has been updated. Visit your salary slip to review the details.');

        $this->post(route('salary-notifications.read', $notification))
            ->assertRedirect(route('salary-slip.show'))
            ->assertSessionHas('status', 'Salary notification marked as read.');

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_salary_update_does_not_notify_when_salary_is_unchanged(): void
    {
        $admin = User::factory()->administrator()->create();
        $employee = User::factory()->create([
            'monthly_salary' => '68000.50',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.staff.salary', $employee), [
                'monthly_salary' => '68000.50',
            ])
            ->assertRedirect(route('admin.staff.show', $employee));

        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_admin_can_review_each_salary_change_and_who_made_it(): void
    {
        $admin = User::factory()->administrator()->create([
            'name' => 'Payroll Admin',
        ]);
        $employee = User::factory()->create();

        $this->actingAs($admin)
            ->patch(route('admin.staff.salary', $employee), [
                'monthly_salary' => '68000.50',
            ])
            ->assertRedirect(route('admin.staff.show', $employee));

        $this->patch(route('admin.staff.salary', $employee), [
            'monthly_salary' => '72000.00',
        ])->assertRedirect(route('admin.staff.show', $employee));

        $this->get(route('admin.staff.show', $employee))
            ->assertOk()
            ->assertSee('Salary history')
            ->assertSee('₹68,000.50')
            ->assertSee('₹72,000.00')
            ->assertSee('Salary assigned')
            ->assertSee('Salary changed')
            ->assertSee('Payroll Admin');

        $this->assertDatabaseCount('salary_histories', 2);
        $this->assertDatabaseHas('salary_histories', [
            'user_id' => $employee->id,
            'changed_by' => $admin->id,
            'monthly_salary' => '72000.00',
            'change_type' => 'updated',
        ]);
    }

    public function test_employee_can_view_only_their_own_salary_history(): void
    {
        $admin = User::factory()->administrator()->create([
            'name' => 'Payroll Admin',
        ]);
        $employee = User::factory()->create();
        $coworker = User::factory()->create();

        $this->actingAs($admin)
            ->patch(route('admin.staff.salary', $employee), [
                'monthly_salary' => '12000.00',
            ])
            ->assertRedirect(route('admin.staff.show', $employee));

        $this->patch(route('admin.staff.salary', $coworker), [
            'monthly_salary' => '98000.00',
        ])->assertRedirect(route('admin.staff.show', $coworker));

        $this->actingAs($employee)
            ->get(route('salary-slip.show'))
            ->assertOk()
            ->assertSee('₹12,000.00')
            ->assertSee('Payroll Admin')
            ->assertDontSee('₹98,000.00');
    }

    public function test_employee_cannot_update_an_employees_monthly_salary(): void
    {
        $employee = User::factory()->create();
        $coworker = User::factory()->create([
            'monthly_salary' => '50000.00',
        ]);

        $this->actingAs($employee)
            ->patch(route('admin.staff.salary', $coworker), [
                'monthly_salary' => '1.00',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('users', [
            'id' => $coworker->id,
            'monthly_salary' => '50000.00',
        ]);
    }

    public function test_admin_cannot_save_a_negative_monthly_salary(): void
    {
        $admin = User::factory()->administrator()->create();
        $employee = User::factory()->create([
            'monthly_salary' => '50000.00',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.staff.show', $employee))
            ->patch(route('admin.staff.salary', $employee), [
                'monthly_salary' => '-1.00',
            ])
            ->assertRedirect(route('admin.staff.show', $employee))
            ->assertSessionHasErrors('monthly_salary');

        $this->assertDatabaseHas('users', [
            'id' => $employee->id,
            'monthly_salary' => '50000.00',
        ]);
    }
}
