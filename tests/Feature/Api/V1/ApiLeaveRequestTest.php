<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiLeaveRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_submit_and_view_their_own_leave_request(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherUser->leaveRequests()->create([
            'leave_type' => 'sick',
            'start_date' => '2026-11-09',
            'end_date' => '2026-11-09',
            'reason' => 'Private medical leave',
        ]);
        $this->travelTo('2026-11-06 09:00:00');

        $created = $this->actingAs($user)
            ->postJson('/api/v1/leave-requests', [
                'leave_type' => 'annual',
                'start_date' => '2026-11-09',
                'end_date' => '2026-11-10',
                'reason' => 'Family event',
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending');

        $this->getJson('/api/v1/leave-requests')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.reason', 'Family event');

        $this->assertDatabaseHas('leave_requests', [
            'id' => $created->json('data.id'),
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
    }

    public function test_employee_leave_request_rejects_an_end_date_before_the_start_date(): void
    {
        $user = User::factory()->create();
        $this->travelTo('2026-11-06 09:00:00');

        $this->actingAs($user)
            ->postJson('/api/v1/leave-requests', [
                'leave_type' => 'annual',
                'start_date' => '2026-11-10',
                'end_date' => '2026-11-09',
                'reason' => 'Family event',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('end_date');

        $this->assertDatabaseCount('leave_requests', 0);
    }

    public function test_admin_cannot_submit_an_employee_leave_request(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin)
            ->postJson('/api/v1/leave-requests', [
                'leave_type' => 'annual',
                'start_date' => '2026-11-09',
                'end_date' => '2026-11-09',
                'reason' => 'Family event',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('leave_requests', 0);
    }
}
