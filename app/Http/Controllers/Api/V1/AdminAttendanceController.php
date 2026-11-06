<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class AdminAttendanceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'report' => ['nullable', Rule::in(['daily', 'weekly', 'monthly', 'absent'])],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $reportType = $validated['report'] ?? 'daily';
        $reportDate = $validated['date'] ?? today()->toDateString();
        $selectedDate = Carbon::createFromFormat('Y-m-d', $reportDate);
        $rangeStart = $selectedDate->copy();
        $rangeEnd = $selectedDate->copy();

        if ($reportType === 'weekly') {
            $rangeStart->startOfWeek(Carbon::MONDAY);
            $rangeEnd->endOfWeek(Carbon::SUNDAY);
        } elseif (in_array($reportType, ['monthly', 'absent'], true)) {
            $rangeStart->startOfMonth();
            $rangeEnd->endOfMonth();
        }

        $rangeStartDate = $rangeStart->toDateString();
        $rangeEndDate = $rangeEnd->toDateString();
        $workingDates = $this->workingDatesBetween($rangeStart, $rangeEnd);
        $workingDateLookup = array_fill_keys($workingDates, true);
        $staff = User::query()
            ->with([
                'attendances' => fn (HasMany $query) => $query
                    ->whereDate('attendance_date', '>=', $rangeStartDate)
                    ->whereDate('attendance_date', '<=', $rangeEndDate),
                'leaveRequests' => fn (HasMany $query) => $query
                    ->where('status', 'approved')
                    ->whereDate('start_date', '<=', $rangeEndDate)
                    ->whereDate('end_date', '>=', $rangeStartDate),
            ])
            ->orderBy('name')
            ->get();
        $summary = [
            'office' => 0,
            'wfh' => 0,
            'leave' => 0,
            'absent' => 0,
            'late' => 0,
        ];
        $rows = collect();

        foreach ($staff as $staffMember) {
            $attendanceByDate = $staffMember->attendances
                ->keyBy(fn (Attendance $attendance): string => $attendance->attendance_date->toDateString());
            $leaveRequests = $staffMember->leaveRequests;
            $leaveDates = [];
            $absentDates = [];

            foreach ($workingDates as $workingDate) {
                $attendance = $attendanceByDate->get($workingDate);

                if ($attendance?->check_in_at !== null) {
                    $summary[$attendance->work_location === 'wfh' ? 'wfh' : 'office']++;

                    if ($attendance->is_late) {
                        $summary['late']++;
                    }

                    continue;
                }

                if ($leaveRequests->contains(
                    fn ($leaveRequest): bool => $leaveRequest->start_date->toDateString() <= $workingDate
                        && $leaveRequest->end_date->toDateString() >= $workingDate,
                )) {
                    $leaveDates[] = $workingDate;
                    $summary['leave']++;

                    continue;
                }

                $absentDates[] = $workingDate;
                $summary['absent']++;
            }

            if ($reportType === 'daily') {
                $attendance = $attendanceByDate->first();
                $isPresent = $attendance?->check_in_at !== null;
                $isOnLeave = in_array($reportDate, $leaveDates, true);
                $rows->push([
                    'employee' => [
                        'id' => $staffMember->id,
                        'name' => $staffMember->name,
                        'email' => $staffMember->email,
                        'role' => $staffMember->is_admin ? 'admin' : 'employee',
                    ],
                    'attendance' => $attendance === null ? null : [
                        'id' => $attendance->id,
                        'work_location' => $attendance->work_location,
                        'is_late' => (bool) $attendance->is_late,
                        'check_in_at' => $attendance->check_in_at?->toISOString(),
                        'check_out_at' => $attendance->check_out_at?->toISOString(),
                    ],
                    'status' => $isPresent
                        ? ($attendance->check_out_at ? 'complete' : 'in_progress')
                        : ($isOnLeave ? 'on_leave' : (in_array($reportDate, $absentDates, true) ? 'absent' : 'not_scheduled')),
                ]);

                continue;
            }

            if ($reportType === 'absent') {
                foreach ($absentDates as $absentDate) {
                    $rows->push([
                        'employee' => [
                            'id' => $staffMember->id,
                            'name' => $staffMember->name,
                            'email' => $staffMember->email,
                        ],
                        'date' => $absentDate,
                    ]);
                }

                continue;
            }

            $rows->push([
                'employee' => [
                    'id' => $staffMember->id,
                    'name' => $staffMember->name,
                    'email' => $staffMember->email,
                    'role' => $staffMember->is_admin ? 'admin' : 'employee',
                ],
                'office_days' => $attendanceByDate->filter(fn (Attendance $attendance): bool => $attendance->check_in_at !== null
                    && $attendance->work_location !== 'wfh'
                    && isset($workingDateLookup[$attendance->attendance_date->toDateString()]))->count(),
                'wfh_days' => $attendanceByDate->filter(fn (Attendance $attendance): bool => $attendance->check_in_at !== null
                    && $attendance->work_location === 'wfh'
                    && isset($workingDateLookup[$attendance->attendance_date->toDateString()]))->count(),
                'late_days' => $attendanceByDate->filter(fn (Attendance $attendance): bool => $attendance->check_in_at !== null
                    && $attendance->is_late
                    && isset($workingDateLookup[$attendance->attendance_date->toDateString()]))->count(),
                'leave_days' => count($leaveDates),
                'absent_days' => count($absentDates),
            ]);
        }

        $page = $validated['page'] ?? 1;
        $perPage = 20;
        $paginator = new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()],
        );

        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'report' => $reportType,
                'date' => $reportDate,
                'range_start' => $rangeStartDate,
                'range_end' => $rangeEndDate,
                'scheduled_workdays' => count($workingDates),
                'staff_count' => $staff->count(),
                'summary' => $summary,
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    /**
     * @return list<string>
     */
    private function workingDatesBetween(Carbon $rangeStart, Carbon $rangeEnd): array
    {
        $holidayDates = collect(range($rangeStart->year, $rangeEnd->year))
            ->flatMap(fn (int $year): array => collect(config("holidays.india.{$year}", []))
                ->pluck('date')
                ->all())
            ->flip();
        $workingDates = [];

        for ($date = $rangeStart->copy(); $date->lte($rangeEnd); $date->addDay()) {
            $dateString = $date->toDateString();

            if ($date->isWeekday() && ! $holidayDates->has($dateString)) {
                $workingDates[] = $dateString;
            }
        }

        return $workingDates;
    }
}
