<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminLeaveRequestController extends Controller
{
    public function index(): View
    {
        return view('admin.leave-requests.index', [
            'leaveRequests' => LeaveRequest::query()
                ->with(['user', 'reviewer'])
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->paginate(20),
            'leaveTypes' => [
                'annual' => 'Annual',
                'sick' => 'Sick',
                'personal' => 'Personal',
                'other' => 'Other',
            ],
        ]);
    }

    public function update(Request $request, LeaveRequest $leaveRequest): RedirectResponse
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
            return back()->withErrors([
                'status' => 'This leave request has already been reviewed.',
            ]);
        }

        $message = $validated['status'] === 'approved'
            ? 'The leave request has been approved.'
            : 'The leave request has been rejected.';

        return redirect()->route('admin.leave-requests.index')->with('status', $message);
    }
}
