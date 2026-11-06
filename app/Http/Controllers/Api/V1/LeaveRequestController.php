<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\LeaveRequestResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class LeaveRequestController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return LeaveRequestResource::collection(
            $request->user()->leaveRequests()
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->paginate(20),
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'leave_type' => ['required', Rule::in(['annual', 'sick', 'personal', 'other'])],
            'start_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        return LeaveRequestResource::make(
            $request->user()->leaveRequests()->create($validated)->refresh(),
        )->response()->setStatusCode(201);
    }
}
