<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\LeaveRequestResource;
use App\Models\LeaveRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class AdminLeaveRequestController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return LeaveRequestResource::collection(
            LeaveRequest::query()
                ->with(['user', 'reviewer'])
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->paginate(20),
        );
    }

    public function update(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected'])],
        ]);
        $updated = LeaveRequest::query()
            ->whereKey($leaveRequest->getKey())
            ->where('status', 'pending')
            ->update([
                'status' => $validated['status'],
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);

        if ($updated === 0) {
            return response()->json([
                'message' => 'This leave request has already been reviewed.',
            ], 409);
        }

        return LeaveRequestResource::make($leaveRequest->fresh()->load(['user', 'reviewer']))->response();
    }
}
