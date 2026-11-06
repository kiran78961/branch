<?php

namespace App\Http\Controllers;

use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $today = today()->toDateString();
        $todayAttendance = $user->attendances()
            ->whereDate('attendance_date', $today)
            ->first();
        $hasCheckedIn = $todayAttendance?->check_in_at !== null;
        $hasApprovedLeave = $user->leaveRequests()
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->exists();
        $isHoliday = collect(config('holidays.india.'.substr($today, 0, 4), []))
            ->contains(fn (array $holiday): bool => $holiday['date'] === $today);
        $isWorkingDay = today()->isWeekday() && ! $isHoliday;

        $lunchStart = Carbon::createFromFormat('H:i', substr($user->lunch_start_time ?? '13:00', 0, 5), 'Asia/Kolkata')
            ->format('g:i A');
        $lunchEnd = Carbon::createFromFormat('H:i', substr($user->lunch_end_time ?? '14:00', 0, 5), 'Asia/Kolkata')
            ->format('g:i A');

        return view('attendance.index', [
            'todayAttendance' => $todayAttendance,
            'lunchSchedule' => "{$lunchStart}–{$lunchEnd}",
            'dailyWorkHours' => (int) $user->daily_work_hours,
            'dailyWorkTargetSeconds' => (int) $user->daily_work_hours * 3600,
            'presentCount' => $hasCheckedIn && $todayAttendance->work_location === 'office' ? 1 : 0,
            'wfhCount' => $hasCheckedIn && $todayAttendance->work_location === 'wfh' ? 1 : 0,
            'leaveCount' => $hasApprovedLeave && ! $hasCheckedIn ? 1 : 0,
            'absentCount' => $isWorkingDay && ! $hasCheckedIn && ! $hasApprovedLeave ? 1 : 0,
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
        $validated = $request->validate([
            'work_location' => ['nullable', Rule::in(['office', 'wfh'])],
        ]);
        $user = $request->user();
        $attendanceDate = today()->toDateString();
        $companySettings = DB::table('company_settings')
            ->where('id', 1)
            ->firstOrFail(['workday_start_time']);
        $workdayStartTime = substr($companySettings->workday_start_time, 0, 5).':00';
        $isLate = now('Asia/Kolkata')->format('H:i:s') > $workdayStartTime;

        if ($user->attendances()->whereDate('attendance_date', $attendanceDate)->exists()) {
            return redirect()->route('attendance.index')->withErrors([
                'attendance' => 'You have already checked in today.',
            ]);
        }

        try {
            $user->attendances()->create([
                'attendance_date' => $attendanceDate,
                'work_location' => $validated['work_location'] ?? 'office',
                'is_late' => $isLate,
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

        if ($attendance->lunch_started_at !== null && $attendance->lunch_ended_at === null) {
            return redirect()->route('attendance.index')->withErrors([
                'attendance' => 'End your lunch break before checking out.',
            ]);
        }

        $updated = $request->user()->attendances()
            ->whereKey($attendance->getKey())
            ->whereNull('check_out_at')
            ->where(fn ($query) => $query->whereNull('lunch_started_at')->orWhereNotNull('lunch_ended_at'))
            ->update(['check_out_at' => now()]);

        if ($updated === 0) {
            return redirect()->route('attendance.index')->withErrors([
                'attendance' => 'Your attendance could not be checked out. Refresh and try again.',
            ]);
        }

        return redirect()->route('attendance.index')->with('status', 'You are checked out for today.');
    }

    public function startLunch(Request $request): RedirectResponse
    {
        $attendance = $request->user()->attendances()
            ->whereDate('attendance_date', today()->toDateString())
            ->first();

        if ($attendance === null || $attendance->check_in_at === null) {
            return redirect()->route('attendance.index')->withErrors([
                'attendance' => 'Check in before starting your lunch break.',
            ]);
        }

        if ($attendance->check_out_at !== null) {
            return redirect()->route('attendance.index')->withErrors([
                'attendance' => 'You cannot start lunch after checking out.',
            ]);
        }

        if ($attendance->lunch_started_at !== null) {
            return redirect()->route('attendance.index')->withErrors([
                'attendance' => $attendance->lunch_ended_at === null
                    ? 'Your lunch break is already in progress.'
                    : 'You have already taken your lunch break today.',
            ]);
        }

        $updated = $request->user()->attendances()
            ->whereKey($attendance->getKey())
            ->whereNull('lunch_started_at')
            ->whereNull('check_out_at')
            ->update(['lunch_started_at' => now()]);

        if ($updated === 0) {
            return redirect()->route('attendance.index')->withErrors([
                'attendance' => 'Your lunch break could not be started. Refresh and try again.',
            ]);
        }

        return redirect()->route('attendance.index')->with('status', 'Your lunch break has started.');
    }

    public function endLunch(Request $request): RedirectResponse
    {
        $attendance = $request->user()->attendances()
            ->whereDate('attendance_date', today()->toDateString())
            ->first();

        if ($attendance === null || $attendance->check_in_at === null) {
            return redirect()->route('attendance.index')->withErrors([
                'attendance' => 'Check in before ending your lunch break.',
            ]);
        }

        if ($attendance->lunch_started_at === null) {
            return redirect()->route('attendance.index')->withErrors([
                'attendance' => 'Start your lunch break before ending it.',
            ]);
        }

        if ($attendance->lunch_ended_at !== null) {
            return redirect()->route('attendance.index')->withErrors([
                'attendance' => 'Your lunch break has already ended.',
            ]);
        }

        $updated = $request->user()->attendances()
            ->whereKey($attendance->getKey())
            ->whereNotNull('lunch_started_at')
            ->whereNull('lunch_ended_at')
            ->update(['lunch_ended_at' => now()]);

        if ($updated === 0) {
            return redirect()->route('attendance.index')->withErrors([
                'attendance' => 'Your lunch break could not be ended. Refresh and try again.',
            ]);
        }

        return redirect()->route('attendance.index')->with('status', 'Your lunch break has ended.');
    }
}
