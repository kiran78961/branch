<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    public function index(Request $request): View
    {
        return view('leave-requests.index', [
            'leaveRequests' => $request->user()->leaveRequests()
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->paginate(15),
            'leaveTypes' => [
                'annual' => 'Annual',
                'sick' => 'Sick',
                'personal' => 'Personal',
                'other' => 'Other',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'leave_type' => ['required', Rule::in(['annual', 'sick', 'personal', 'other'])],
            'start_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        $request->user()->leaveRequests()->create($validated);

        return redirect()->route('leave-requests.index')
            ->with('status', 'Your leave request has been submitted for review.');
    }
}
