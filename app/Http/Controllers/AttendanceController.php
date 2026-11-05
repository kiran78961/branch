<?php

namespace App\Http\Controllers;

use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $today = today()->toDateString();

        return view('attendance.index', [
            'todayAttendance' => $user->attendances()
                ->whereDate('attendance_date', $today)
                ->first(),
        ]);
    }

    public function history(Request $request): View
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $attendanceRecords = $request->user()->attendances()
            ->when(
                isset($validated['date']),
                fn ($query) => $query->whereDate('attendance_date', $validated['date']),
            )
            ->orderByDesc('attendance_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('attendance.history', [
            'attendanceRecords' => $attendanceRecords,
            'selectedDate' => $validated['date'] ?? '',
        ]);
    }

    public function checkIn(Request $request): RedirectResponse
    {
        $user = $request->user();
        $attendanceDate = today()->toDateString();

        if ($user->attendances()->whereDate('attendance_date', $attendanceDate)->exists()) {
            return redirect()->route('attendance.index')->withErrors([
                'attendance' => 'You have already checked in today.',
            ]);
        }

        try {
            $user->attendances()->create([
                'attendance_date' => $attendanceDate,
                'check_in_at' => now(),
            ]);
        } catch (UniqueConstraintViolationException) {
            return redirect()->route('attendance.index')->withErrors([
                'attendance' => 'You have already checked in today.',
            ]);
        }

        return redirect()->route('attendance.index')->with('status', 'You are checked in for today.');
    }

    public function checkOut(Request $request): RedirectResponse
    {
        $attendance = $request->user()->attendances()
            ->whereDate('attendance_date', today()->toDateString())
            ->first();

        if ($attendance === null || $attendance->check_in_at === null) {
            return redirect()->route('attendance.index')->withErrors([
                'attendance' => 'Check in before checking out.',
            ]);
        }

        if ($attendance->check_out_at !== null) {
            return redirect()->route('attendance.index')->withErrors([
                'attendance' => 'You have already checked out today.',
            ]);
        }

        $updated = $request->user()->attendances()
            ->whereKey($attendance->getKey())
            ->whereNull('check_out_at')
            ->update(['check_out_at' => now()]);

        if ($updated === 0) {
            return redirect()->route('attendance.index')->withErrors([
                'attendance' => 'You have already checked out today.',
            ]);
        }

        return redirect()->route('attendance.index')->with('status', 'You are checked out for today.');
    }
}
