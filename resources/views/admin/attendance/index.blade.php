@extends('layouts.attendance')

@section('title', 'Team attendance')

@section('content')
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-medium text-[#748178]">Administrator workspace</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-[-0.035em] text-[#18221e] sm:text-4xl">Attendance reports</h1>
            <p class="mt-2 text-sm leading-6 text-[#748178]">Review daily, weekly, monthly, and absent employee reports.</p>
        </div>
        <form method="GET" action="{{ route('admin.attendance.index') }}" class="flex flex-wrap items-end gap-3">
            <div class="space-y-1.5">
                <label for="report" class="block text-xs font-semibold uppercase tracking-wide text-[#748178]">Report type</label>
                <select id="report" name="report" class="h-11 rounded-lg border border-[#dce2dc] bg-white px-3 text-sm text-[#18221e] outline-none focus:border-[#527863] focus:ring-4 focus:ring-[#527863]/10">
                    <option value="daily" @selected($reportType === 'daily')>Daily attendance</option>
                    <option value="weekly" @selected($reportType === 'weekly')>Weekly attendance</option>
                    <option value="monthly" @selected($reportType === 'monthly')>Monthly attendance</option>
                    <option value="absent" @selected($reportType === 'absent')>Absent employees</option>
                </select>
            </div>
            <div class="space-y-1.5">
                <label for="date" class="block text-xs font-semibold uppercase tracking-wide text-[#748178]">Select date</label>
                <input id="date" name="date" type="date" value="{{ $reportDate }}" required class="h-11 rounded-lg border border-[#dce2dc] bg-white px-3 text-sm text-[#18221e] outline-none focus:border-[#527863] focus:ring-4 focus:ring-[#527863]/10">
            </div>
            <button type="submit" class="h-11 rounded-lg bg-[#244b3b] px-4 text-sm font-semibold text-white transition hover:bg-[#193d30] focus:outline-none focus:ring-4 focus:ring-[#527863]/20">View report</button>
        </form>
    </div>

    <p class="mt-5 text-sm text-[#748178]">
        {{ $rangeLabel }} · {{ $staffCount }} staff · {{ $workingDayCount }} scheduled workdays
        @if ($reportType === 'weekly')
            <span>(Monday–Sunday)</span>
        @elseif ($reportType === 'absent')
            <span>(approved leave, weekends, and public holidays are excluded)</span>
        @endif
    </p>

    <section class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-5" aria-label="Attendance summary">
        <div data-report-summary-card class="rounded-2xl border border-[#d7eee3] bg-gradient-to-br from-white to-emerald-50 p-5 shadow-sm">
            <p class="text-sm text-[#748178]">{{ $reportType === 'daily' ? 'Present' : 'Office days' }}</p>
            <p class="mt-2 text-3xl font-semibold tracking-tight text-[#18221e]">{{ $summary['office'] }}</p>
            <p class="mt-1 text-xs text-[#748178]">On-site</p>
        </div>
        <div data-report-summary-card class="rounded-2xl border border-[#d8e6fa] bg-gradient-to-br from-white to-blue-50 p-5 shadow-sm">
            <p class="text-sm text-[#748178]">{{ $reportType === 'daily' ? 'WFH' : 'WFH days' }}</p>
            <p class="mt-2 text-3xl font-semibold tracking-tight text-[#18221e]">{{ $summary['wfh'] }}</p>
            <p class="mt-1 text-xs text-[#748178]">Working remotely</p>
        </div>
        <div data-report-summary-card class="rounded-2xl border border-[#f5e1c8] bg-gradient-to-br from-white to-orange-50 p-5 shadow-sm">
            <p class="text-sm text-[#748178]">{{ $reportType === 'daily' ? 'On leave' : 'Leave days' }}</p>
            <p class="mt-2 text-3xl font-semibold tracking-tight text-[#18221e]">{{ $summary['leave'] }}</p>
            <p class="mt-1 text-xs text-[#748178]">Approved leave</p>
        </div>
        <div data-report-summary-card class="rounded-2xl border border-[#f5d9e2] bg-gradient-to-br from-white to-rose-50 p-5 shadow-sm">
            <p class="text-sm text-[#748178]">{{ $reportType === 'daily' ? 'Absent' : 'Absent days' }}</p>
            <p class="mt-2 text-3xl font-semibold tracking-tight text-[#18221e]">{{ $summary['absent'] }}</p>
            <p class="mt-1 text-xs text-[#748178]">Unplanned absence</p>
        </div>
        <div data-report-summary-card class="rounded-2xl border border-[#f5e4bd] bg-gradient-to-br from-white to-amber-50 p-5 shadow-sm">
            <p class="text-sm text-[#748178]">{{ $reportType === 'daily' ? 'Late' : 'Late days' }}</p>
            <p class="mt-2 text-3xl font-semibold tracking-tight text-[#18221e]">{{ $summary['late'] }}</p>
            <p class="mt-1 text-xs text-[#748178]">Employee arrived late</p>
        </div>
    </section>

    <section class="mt-9" aria-labelledby="attendance-report-title">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 id="attendance-report-title" class="text-xl font-semibold tracking-tight text-[#18221e]">
                    @if ($reportType === 'absent')
                        Absent employees
                    @elseif ($reportType === 'daily')
                        Daily attendance
                    @else
                        {{ ucfirst($reportType) }} attendance
                    @endif
                </h2>
                <p class="mt-1 text-sm text-[#748178]">{{ $rangeLabel }}</p>
            </div>
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('admin.staff.create') }}" class="text-sm font-semibold text-[#527863] transition hover:text-[#294b3b]">Add employee +</a>
                <a href="{{ route('admin.staff.index') }}" class="text-sm font-semibold text-[#527863] transition hover:text-[#294b3b]">Manage staff →</a>
            </div>
        </div>

        <div class="mt-5 overflow-hidden rounded-2xl border border-[#e4e9e3] bg-white shadow-sm">
            <div class="overflow-x-auto">
                @if ($reportType === 'absent')
                    <table class="w-full min-w-[560px] text-left text-sm">
                        <thead class="bg-[#f7f8f5] text-xs font-semibold uppercase tracking-wide text-[#748178]">
                            <tr>
                                <th scope="col" class="px-5 py-4">Employee</th>
                                <th scope="col" class="px-5 py-4">Role</th>
                                <th scope="col" class="px-5 py-4">Absent date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#edf0ec]">
                            @forelse ($reportRows as $row)
                                @php($staffMember = $row['staffMember'])
                                <tr>
                                    <td class="px-5 py-4">
                                        <a href="{{ route('admin.staff.show', $staffMember) }}" class="font-semibold text-[#315d48] transition hover:text-[#173b32] hover:underline">{{ $staffMember->name }}</a>
                                        <p class="mt-1 text-xs text-[#748178]">{{ $staffMember->email }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-[#58645c]">{{ $staffMember->is_admin ? 'Administrator' : 'Employee' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-[#58645c]">{{ \Illuminate\Support\Carbon::parse($row['date'])->format('M j, Y') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-5 py-10 text-center text-sm text-[#748178]">No unplanned absences in this period.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                @elseif ($reportType === 'daily')
                    <table class="w-full min-w-[980px] text-left text-sm">
                        <thead class="bg-[#f7f8f5] text-xs font-semibold uppercase tracking-wide text-[#748178]">
                            <tr>
                                <th scope="col" class="px-5 py-4">Employee</th>
                                <th scope="col" class="px-5 py-4">Role</th>
                                <th scope="col" class="px-5 py-4">Work location</th>
                                <th scope="col" class="px-5 py-4">Check in</th>
                                <th scope="col" class="px-5 py-4">Check out</th>
                                <th scope="col" class="px-5 py-4">Arrival</th>
                                <th scope="col" class="px-5 py-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#edf0ec]">
                            @forelse ($reportRows as $staffMember)
                                @php($attendance = $staffMember->attendances->first())
                                @php($status = $attendance?->check_in_at ? ($attendance->check_out_at ? 'Complete' : 'In progress') : ($staffMember->report_leave_days > 0 ? 'On leave' : ($staffMember->report_absent_days > 0 ? 'Absent' : 'Not scheduled')))
                                @php($isLate = $attendance?->check_in_at && $attendance->is_late)
                                <tr>
                                    <td class="px-5 py-4">
                                        <a href="{{ route('admin.staff.show', $staffMember) }}" class="font-semibold text-[#315d48] transition hover:text-[#173b32] hover:underline">{{ $staffMember->name }}</a>
                                        <p class="mt-1 text-xs text-[#748178]">{{ $staffMember->email }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-[#58645c]">{{ $staffMember->is_admin ? 'Administrator' : 'Employee' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-[#58645c]">{{ $attendance?->check_in_at ? ($attendance->work_location === 'wfh' ? 'WFH' : 'Office') : '—' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-[#58645c]">{{ $attendance?->check_in_at?->setTimezone('Asia/Kolkata')->format('g:i A') ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-[#58645c]">{{ $attendance?->check_out_at?->setTimezone('Asia/Kolkata')->format('g:i A') ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        @if ($isLate)
                                            <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">Late</span>
                                        @elseif ($attendance?->check_in_at)
                                            <span class="text-[#58645c]">On time</span>
                                        @else
                                            <span class="text-[#58645c]">—</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $status === 'Complete' ? 'bg-[#edf5ee] text-[#315d48]' : (in_array($status, ['In progress', 'On leave'], true) ? 'bg-[#fbf2e8] text-[#8b5a2a]' : 'bg-[#f0f1ee] text-[#68736b]') }}">{{ $status }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-5 py-10 text-center text-sm text-[#748178]">No staff accounts are registered yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                @else
                    <table class="w-full min-w-[900px] text-left text-sm">
                        <thead class="bg-[#f7f8f5] text-xs font-semibold uppercase tracking-wide text-[#748178]">
                            <tr>
                                <th scope="col" class="px-5 py-4">Employee</th>
                                <th scope="col" class="px-5 py-4">Role</th>
                                <th scope="col" class="px-5 py-4">Office days</th>
                                <th scope="col" class="px-5 py-4">WFH days</th>
                                <th scope="col" class="px-5 py-4">Late days</th>
                                <th scope="col" class="px-5 py-4">Leave days</th>
                                <th scope="col" class="px-5 py-4">Absent days</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#edf0ec]">
                            @forelse ($reportRows as $staffMember)
                                <tr>
                                    <td class="px-5 py-4">
                                        <a href="{{ route('admin.staff.show', $staffMember) }}" class="font-semibold text-[#315d48] transition hover:text-[#173b32] hover:underline">{{ $staffMember->name }}</a>
                                        <p class="mt-1 text-xs text-[#748178]">{{ $staffMember->email }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-[#58645c]">{{ $staffMember->is_admin ? 'Administrator' : 'Employee' }}</td>
                                    <td class="px-5 py-4 text-[#58645c]">{{ $staffMember->report_office_days }}</td>
                                    <td class="px-5 py-4 text-[#58645c]">{{ $staffMember->report_wfh_days }}</td>
                                    <td class="px-5 py-4 text-[#58645c]">{{ $staffMember->report_late_days }}</td>
                                    <td class="px-5 py-4 text-[#58645c]">{{ $staffMember->report_leave_days }}</td>
                                    <td class="px-5 py-4 text-[#58645c]">{{ $staffMember->report_absent_days }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-5 py-10 text-center text-sm text-[#748178]">No staff accounts are registered yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
        <div class="mt-4">{{ $reportRows->links() }}</div>
    </section>
@endsection
