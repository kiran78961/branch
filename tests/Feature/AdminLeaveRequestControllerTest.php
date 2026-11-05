<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLeaveRequestControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_approve_and_reject_pending_leave_requests(): void
    {
        $admin = User::factory()->administrator()->create();
        $employee = User::factory()->create();
        $approvedRequest = $employee->leaveRequests()->create([
            'leave_type' => 'annual',
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-11',
            'reason' => 'Family trip',
        ]);
        $rejectedRequest = $employee->leaveRequests()->create([
            'leave_type' => 'sick',
            'start_date' => '2026-11-12',
            'end_date' => '2026-11-12',
            'reason' => 'Medical appointment',
        ]);
        $this->travelTo('2026-11-05 15:00:00');

        $this->actingAs($admin)
            ->patch(route('admin.leave-requests.update', $approvedRequest), ['status' => 'approved'])
            ->assertRedirect(route('admin.leave-requests.index'))
            ->assertSessionHas('status', 'The leave request has been approved.');
        $this->actingAs($admin)
            ->patch(route('admin.leave-requests.update', $rejectedRequest), ['status' => 'rejected'])
            ->assertRedirect(route('admin.leave-requests.index'))
            ->assertSessionHas('status', 'The leave request has been rejected.');

        $this->assertDatabaseHas('leave_requests', [
            'id' => $approvedRequest->id,
            'status' => 'approved',
            'reviewed_by' => $admin->id,
            'reviewed_at' => '2026-11-05 15:00:00',
        ]);
        $this->assertDatabaseHas('leave_requests', [
            'id' => $rejectedRequest->id,
            'status' => 'rejected',
            'reviewed_by' => $admin->id,
            'reviewed_at' => '2026-11-05 15:00:00',
        ]);
    }

    public function test_administrator_can_view_and_review_employee_leave_requests(): void
    {
        $admin = User::factory()->administrator()->create();
        $employee = User::factory()->create(['name' => 'Taylor Worker']);
        $employee->leaveRequests()->create([
            'leave_type' => 'personal',
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-10',
            'reason' => 'A day away',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.leave-requests.index'))
            ->assertOk()
            ->assertSee('Taylor Worker')
            ->assertSee('A day away')
            ->assertSee('Approve')
            ->assertSee('href="'.route('admin.leave-requests.index').'"', false)
            ->assertViewHas('leaveRequests', fn ($leaveRequests): bool => $leaveRequests->total() === 1);
    }

    public function test_administrator_success_message_uses_a_dark_mode_visible_text_color(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin)
            ->withSession(['status' => 'The leave request has been approved.'])
            ->get(route('admin.leave-requests.index'))
            ->assertOk()
            ->assertSee('The leave request has been approved.')
            ->assertSee('text-[#315d48]', false);
    }

    public function test_administrator_leave_request_page_escapes_employee_reason(): void
    {
        $admin = User::factory()->administrator()->create();
        $employee = User::factory()->create();
        $employee->leaveRequests()->create([
            'leave_type' => 'other',
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-10',
            'reason' => '<script>alert(1)</script>',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.leave-requests.index'))
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_administrator_cannot_review_a_leave_request_more_than_once(): void
    {
        $admin = User::factory()->administrator()->create();
        $employee = User::factory()->create();
        $leaveRequest = $employee->leaveRequests()->create([
            'leave_type' => 'annual',
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-10',
            'reason' => 'Annual leave',
        ]);
        $leaveRequest->status = 'approved';
        $leaveRequest->reviewed_by = $admin->id;
        $leaveRequest->reviewed_at = '2026-11-05 10:00:00';
        $leaveRequest->save();

        $this->actingAs($admin)
            ->from(route('admin.leave-requests.index'))
            ->patch(route('admin.leave-requests.update', $leaveRequest), ['status' => 'rejected'])
            ->assertRedirect(route('admin.leave-requests.index'))
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'approved',
            'reviewed_by' => $admin->id,
            'reviewed_at' => '2026-11-05 10:00:00',
        ]);
    }

    public function test_administrator_cannot_submit_an_invalid_leave_decision(): void
    {
        $admin = User::factory()->administrator()->create();
        $leaveRequest = User::factory()->create()->leaveRequests()->create([
            'leave_type' => 'annual',
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-10',
            'reason' => 'Annual leave',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.leave-requests.index'))
            ->patch(route('admin.leave-requests.update', $leaveRequest), ['status' => 'pending'])
            ->assertRedirect(route('admin.leave-requests.index'))
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'pending',
            'reviewed_by' => null,
        ]);
    }

    public function test_employee_cannot_access_administrator_leave_review(): void
    {
        $employee = User::factory()->create();
        $leaveRequest = User::factory()->create()->leaveRequests()->create([
            'leave_type' => 'annual',
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-10',
            'reason' => 'Annual leave',
        ]);

        $this->actingAs($employee)
            ->get(route('admin.leave-requests.index'))
            ->assertForbidden();

        $this->patch(route('admin.leave-requests.update', $leaveRequest), ['status' => 'approved'])
            ->assertForbidden();

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'pending',
            'reviewed_by' => null,
        ]);
    }
}
