<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AttendanceResource;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        return AttendanceResource::collection(
            $request->user()->attendances()
                ->when(
                    isset($validated['date']),
                    fn ($query) => $query->whereDate('attendance_date', $validated['date']),
                )
                ->orderByDesc('attendance_date')
                ->orderByDesc('id')
                ->paginate(20)
                ->withQueryString(),
        );
    }

    public function today(Request $request): JsonResponse
    {
        $user = $request->user();
        $date = today()->toDateString();
        $attendance = $user->attendances()
            ->whereDate('attendance_date', $date)
            ->first();
        $hasCheckedIn = $attendance?->check_in_at !== null;
        $hasApprovedLeave = $user->leaveRequests()
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->exists();
        $isHoliday = collect(config('holidays.india.'.substr($date, 0, 4), []))
            ->contains(fn (array $holiday): bool => $holiday['date'] === $date);
        $isWorkingDay = today()->isWeekday() && ! $isHoliday;
        $companySettings = DB::table('company_settings')
            ->where('id', 1)
            ->firstOrFail(['workday_start_time']);
        $dayStatus = match (true) {
            $hasCheckedIn && $attendance->is_late => 'late',
            $hasCheckedIn && $attendance->work_location === 'wfh' => 'wfh',
            $hasCheckedIn => 'present',
            $hasApprovedLeave => 'on_leave',
            $isWorkingDay => 'absent',
            default => 'not_scheduled',
        };

        return response()->json([
            'data' => [
                'date' => $date,
                'day_status' => $dayStatus,
                'is_late' => (bool) $attendance?->is_late,
                'company_start_time' => substr($companySettings->workday_start_time, 0, 5),
                'daily_work_hours' => (int) $user->daily_work_hours,
                'attendance' => $attendance === null ? null : AttendanceResource::make($attendance)->resolve($request),
            ],
        ]);
    }

    public function checkIn(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'work_location' => ['nullable', Rule::in(['office', 'wfh'])],
        ]);
        $user = $request->user();
        $attendanceDate = today()->toDateString();

        if ($user->attendances()->whereDate('attendance_date', $attendanceDate)->exists()) {
            return $this->failure('You have already checked in today.', 409);
        }

        $companySettings = DB::table('company_settings')
            ->where('id', 1)
            ->firstOrFail(['workday_start_time']);
        $workdayStartTime = substr($companySettings->workday_start_time, 0, 5).':00';
        $isLate = now('Asia/Kolkata')->format('H:i:s') > $workdayStartTime;

        try {
            $attendance = $user->attendances()->create([
                'attendance_date' => $attendanceDate,
                'work_location' => $validated['work_location'] ?? 'office',
                'is_late' => $isLate,
                'check_in_at' => now(),
            ]);
        } catch (UniqueConstraintViolationException) {
            return $this->failure('You have already checked in today.', 409);
        }

        return AttendanceResource::make($attendance)->response()->setStatusCode(201);
    }

    public function checkOut(Request $request): JsonResponse
    {
        $attendance = $this->todayAttendance($request->user());

        if ($attendance === null || $attendance->check_in_at === null) {
            return $this->failure('Check in before checking out.');
        }

        if ($attendance->check_out_at !== null) {
            return $this->failure('You have already checked out today.');
        }

        if ($attendance->lunch_started_at !== null && $attendance->lunch_ended_at === null) {
            return $this->failure('End your lunch break before checking out.');
        }

        $updated = $request->user()->attendances()
            ->whereKey($attendance->getKey())
            ->whereNull('check_out_at')
            ->where(fn ($query) => $query->whereNull('lunch_started_at')->orWhereNotNull('lunch_ended_at'))
            ->update(['check_out_at' => now()]);

        if ($updated === 0) {
            return $this->failure('Your attendance could not be checked out. Refresh and try again.', 409);
        }

        return AttendanceResource::make($attendance->refresh())->response();
    }

    public function startLunch(Request $request): JsonResponse
    {
        $attendance = $this->todayAttendance($request->user());

        if ($attendance === null || $attendance->check_in_at === null) {
            return $this->failure('Check in before starting your lunch break.');
        }

        if ($attendance->check_out_at !== null) {
            return $this->failure('You cannot start lunch after checking out.');
        }

        if ($attendance->lunch_started_at !== null) {
            return $this->failure($attendance->lunch_ended_at === null
                ? 'Your lunch break is already in progress.'
                : 'You have already taken your lunch break today.');
        }

        $updated = $request->user()->attendances()
            ->whereKey($attendance->getKey())
            ->whereNull('lunch_started_at')
            ->whereNull('check_out_at')
            ->update(['lunch_started_at' => now()]);

        if ($updated === 0) {
            return $this->failure('Your lunch break could not be started. Refresh and try again.', 409);
        }

        return AttendanceResource::make($attendance->refresh())->response();
    }

    public function endLunch(Request $request): JsonResponse
    {
        $attendance = $this->todayAttendance($request->user());

        if ($attendance === null || $attendance->check_in_at === null) {
            return $this->failure('Check in before ending your lunch break.');
        }

        if ($attendance->lunch_started_at === null) {
            return $this->failure('Start your lunch break before ending it.');
        }

        if ($attendance->lunch_ended_at !== null) {
            return $this->failure('Your lunch break has already ended.');
        }

        $updated = $request->user()->attendances()
            ->whereKey($attendance->getKey())
            ->whereNotNull('lunch_started_at')
            ->whereNull('lunch_ended_at')
            ->update(['lunch_ended_at' => now()]);

        if ($updated === 0) {
            return $this->failure('Your lunch break could not be ended. Refresh and try again.', 409);
        }

        return AttendanceResource::make($attendance->refresh())->response();
    }

    private function todayAttendance(User $user): ?Attendance
    {
        return $user->attendances()
            ->whereDate('attendance_date', today()->toDateString())
            ->first();
    }

    private function failure(string $message, int $status = 422): JsonResponse
    {
        return response()->json(['message' => $message], $status);
    }
}
