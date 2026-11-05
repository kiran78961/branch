<?php

namespace Tests\Feature;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveRequestControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_submit_a_leave_request_for_admin_review(): void
    {
        $employee = User::factory()->create();
        $anotherEmployee = User::factory()->create();
        $admin = User::factory()->administrator()->create();
        $this->travelTo('2026-11-05 09:00:00');

        $response = $this->actingAs($employee)->post(route('leave-requests.store'), [
            'leave_type' => 'annual',
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-12',
            'reason' => 'Family trip',
            'user_id' => $anotherEmployee->id,
            'status' => 'approved',
            'reviewed_by' => $admin->id,
        ]);

        $response->assertRedirect(route('leave-requests.index'))
            ->assertSessionHas('status', 'Your leave request has been submitted for review.');
        $leaveRequest = LeaveRequest::query()->where('user_id', $employee->id)->firstOrFail();
        $this->assertSame('2026-11-10', $leaveRequest->start_date->toDateString());
        $this->assertSame('2026-11-12', $leaveRequest->end_date->toDateString());
        $this->assertDatabaseHas('leave_requests', [
            'user_id' => $employee->id,
            'leave_type' => 'annual',
            'reason' => 'Family trip',
            'status' => 'pending',
            'reviewed_by' => null,
        ]);
    }

    public function test_employee_only_sees_their_own_leave_requests(): void
    {
        $employee = User::factory()->create();
        $anotherEmployee = User::factory()->create();
        $employee->leaveRequests()->create([
            'leave_type' => 'sick',
            'start_date' => '2026-11-06',
            'end_date' => '2026-11-06',
            'reason' => 'Medical appointment',
        ]);
        $anotherEmployee->leaveRequests()->create([
            'leave_type' => 'personal',
            'start_date' => '2026-11-07',
            'end_date' => '2026-11-07',
            'reason' => 'Private reason for another employee',
        ]);

        $this->actingAs($employee)
            ->get(route('leave-requests.index'))
            ->assertOk()
            ->assertSee('href="'.route('leave-requests.index').'"', false)
            ->assertSee('My requests')
            ->assertSee('Medical appointment')
            ->assertDontSee('Private reason for another employee')
            ->assertViewHas('leaveRequests', fn ($leaveRequests): bool => $leaveRequests->total() === 1);
    }

    public function test_employee_leave_request_page_escapes_the_reason(): void
    {
        $employee = User::factory()->create();
        $employee->leaveRequests()->create([
            'leave_type' => 'other',
            'start_date' => '2026-11-06',
            'end_date' => '2026-11-06',
            'reason' => '<script>alert(1)</script>',
        ]);

        $this->actingAs($employee)
            ->get(route('leave-requests.index'))
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_employee_cannot_submit_leave_with_a_past_start_date(): void
    {
        $employee = User::factory()->create();
        $this->travelTo('2026-11-05 09:00:00');

        $this->actingAs($employee)
            ->from(route('leave-requests.index'))
            ->post(route('leave-requests.store'), [
                'leave_type' => 'annual',
                'start_date' => '2026-11-04',
                'end_date' => '2026-11-05',
                'reason' => 'Past leave',
            ])
            ->assertRedirect(route('leave-requests.index'))
            ->assertSessionHasErrors('start_date');

        $this->assertDatabaseCount('leave_requests', 0);
    }

    public function test_employee_cannot_submit_leave_ending_before_its_start_date(): void
    {
        $employee = User::factory()->create();

        $this->actingAs($employee)
            ->from(route('leave-requests.index'))
            ->post(route('leave-requests.store'), [
                'leave_type' => 'annual',
                'start_date' => '2026-11-10',
                'end_date' => '2026-11-09',
                'reason' => 'Invalid date range',
            ])
            ->assertRedirect(route('leave-requests.index'))
            ->assertSessionHasErrors('end_date');

        $this->assertDatabaseCount('leave_requests', 0);
    }

    public function test_administrator_cannot_apply_for_employee_leave(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin)
            ->get(route('leave-requests.index'))
            ->assertForbidden();

        $this->post(route('leave-requests.store'), [
            'leave_type' => 'annual',
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-10',
            'reason' => 'Test',
        ])->assertForbidden();
    }

    public function test_guest_is_redirected_to_login_before_viewing_leave_requests(): void
    {
        $this->get(route('leave-requests.index'))
            ->assertRedirect(route('login'));
    }
}
