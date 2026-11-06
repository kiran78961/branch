<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminAttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'report' => ['nullable', Rule::in(['daily', 'weekly', 'monthly', 'absent'])],
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $reportType = $validated['report'] ?? 'daily';
        $reportDate = $validated['date'] ?? today()->toDateString();
        $selectedDate = Carbon::createFromFormat('Y-m-d', $reportDate);
        $rangeStart = $selectedDate->copy();
        $rangeEnd = $selectedDate->copy();

        if ($reportType === 'weekly') {
            $rangeStart->startOfWeek(Carbon::MONDAY);
            $rangeEnd->endOfWeek(Carbon::SUNDAY);
        } elseif ($reportType === 'monthly' || $reportType === 'absent') {
            $rangeStart->startOfMonth();
            $rangeEnd->endOfMonth();
        }

        $rangeStartDate = $rangeStart->toDateString();
        $rangeEndDate = $rangeEnd->toDateString();
        $workingDates = $this->workingDatesBetween($rangeStart, $rangeEnd);
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
        $absentRows = collect();

        foreach ($staff as $staffMember) {
            $attendanceByDate = $staffMember->attendances
                ->keyBy(fn (Attendance $attendance): string => $attendance->attendance_date->toDateString());
            $leaveRequests = $staffMember->leaveRequests;
            $leaveDates = [];
            $absentDates = [];

            foreach ($workingDates as $workingDate) {
                $attendance = $attendanceByDate->get($workingDate);

                if ($attendance?->check_in_at !== null) {
                    $location = $attendance->work_location === 'wfh' ? 'wfh' : 'office';
                    $summary[$location]++;

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

            $staffMember->setAttribute('report_absent_dates', $absentDates);
            $staffMember->setAttribute('report_office_days', $attendanceByDate
                ->filter(fn (Attendance $attendance): bool => $attendance->check_in_at !== null
                    && $attendance->work_location !== 'wfh'
                    && in_array($attendance->attendance_date->toDateString(), $workingDates, true))
                ->count());
            $staffMember->setAttribute('report_wfh_days', $attendanceByDate
                ->filter(fn (Attendance $attendance): bool => $attendance->check_in_at !== null
                    && $attendance->work_location === 'wfh'
                    && in_array($attendance->attendance_date->toDateString(), $workingDates, true))
                ->count());
            $staffMember->setAttribute('report_late_days', $attendanceByDate
                ->filter(fn (Attendance $attendance): bool => $attendance->check_in_at !== null
                    && $attendance->is_late
                    && in_array($attendance->attendance_date->toDateString(), $workingDates, true))
                ->count());
            $staffMember->setAttribute('report_leave_days', count($leaveDates));
            $staffMember->setAttribute('report_absent_days', count($staffMember->report_absent_dates ?? []));

            foreach ($staffMember->report_absent_dates as $absentDate) {
                $absentRows->push([
                    'staffMember' => $staffMember,
                    'date' => $absentDate,
                ]);
            }
        }

        $reportRows = $reportType === 'absent' ? $absentRows : $staff;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $reportRows = new LengthAwarePaginator(
            $reportRows->forPage($page, 20)->values(),
            $reportRows->count(),
            20,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()],
        );
        $rangeLabel = match ($reportType) {
            'weekly' => $rangeStart->format('M j, Y').' – '.$rangeEnd->format('M j, Y'),
            'monthly', 'absent' => $rangeStart->format('F Y'),
            default => $selectedDate->format('l, F j, Y'),
        };

        return view('admin.attendance.index', [
            'reportType' => $reportType,
            'reportDate' => $reportDate,
            'rangeStart' => $rangeStartDate,
            'rangeEnd' => $rangeEndDate,
            'rangeLabel' => $rangeLabel,
            'workingDayCount' => count($workingDates),
            'staffCount' => $staff->count(),
            'summary' => $summary,
            'reportRows' => $reportRows,
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

    public function staff(): View
    {
        return view('admin.staff.index', [
            'staff' => User::query()
                ->orderBy('name')
                ->paginate(20),
        ]);
    }

    public function showStaff(User $user): View
    {
        return view('admin.staff.show', [
            'staffMember' => $user,
            'attendances' => $user->attendances()
                ->orderByDesc('attendance_date')
                ->orderByDesc('id')
                ->paginate(15),
        ]);
    }

    public function createStaff(): View
    {
        return view('admin.staff.create');
    }

    public function storeStaff(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        return redirect()->route('admin.staff.show', $user)
            ->with('status', 'Employee created. Share the temporary password with them securely.');
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'is_admin' => ['required', 'boolean'],
        ]);
        $isAdmin = (bool) $validated['is_admin'];

        if ($user->is($request->user()) && ! $isAdmin) {
            return back()->withErrors([
                'is_admin' => 'You cannot remove your own administrator access.',
            ]);
        }

        $user->is_admin = $isAdmin;
        $user->save();

        return redirect()->route('admin.staff.index')->with('status', 'Staff access has been updated.');
    }
}
