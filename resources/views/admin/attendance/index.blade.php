@extends('layouts.attendance')

@section('title', 'Team attendance')

@section('content')
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-medium text-[#748178]">Administrator workspace</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-[-0.035em] text-[#18221e] sm:text-4xl">Team attendance</h1>
            <p class="mt-2 text-sm leading-6 text-[#748178]">Review attendance records for your office.</p>
        </div>
        <form method="GET" action="{{ route('admin.attendance.index') }}" class="flex flex-wrap items-end gap-3">
            <div class="space-y-1.5">
                <label for="date" class="block text-xs font-semibold uppercase tracking-wide text-[#748178]">Report date</label>
                <input id="date" name="date" type="date" value="{{ $reportDate }}" required class="h-11 rounded-lg border border-[#dce2dc] bg-white px-3 text-sm text-[#18221e] outline-none focus:border-[#527863] focus:ring-4 focus:ring-[#527863]/10">
            </div>
            <button type="submit" class="h-11 rounded-lg bg-[#244b3b] px-4 text-sm font-semibold text-white transition hover:bg-[#193d30] focus:outline-none focus:ring-4 focus:ring-[#527863]/20">View report</button>
        </form>
    </div>

    <section class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4" aria-label="Attendance summary">
        <div class="rounded-2xl border border-[#e4e9e3] bg-white p-5 shadow-sm">
            <p class="text-sm text-[#748178]">Total staff</p>
            <p class="mt-2 text-3xl font-semibold tracking-tight text-[#18221e]">{{ $staffCount }}</p>
        </div>
        <div class="rounded-2xl border border-[#e4e9e3] bg-white p-5 shadow-sm">
            <p class="text-sm text-[#748178]">Checked in</p>
            <p class="mt-2 text-3xl font-semibold tracking-tight text-[#315d48]">{{ $checkedInCount }}</p>
        </div>
        <div class="rounded-2xl border border-[#e4e9e3] bg-white p-5 shadow-sm">
            <p class="text-sm text-[#748178]">Checked out</p>
            <p class="mt-2 text-3xl font-semibold tracking-tight text-[#315d48]">{{ $checkedOutCount }}</p>
        </div>
        <div class="rounded-2xl border border-[#e4e9e3] bg-white p-5 shadow-sm">
            <p class="text-sm text-[#748178]">Not started</p>
            <p class="mt-2 text-3xl font-semibold tracking-tight text-[#8b5a2a]">{{ $notStartedCount }}</p>
        </div>
    </section>

    <section class="mt-9" aria-labelledby="attendance-report-title">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 id="attendance-report-title" class="text-xl font-semibold tracking-tight text-[#18221e]">Attendance records</h2>
                <p class="mt-1 text-sm text-[#748178]">{{ \Illuminate\Support\Carbon::parse($reportDate)->format('l, F j, Y') }}</p>
            </div>
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('admin.staff.create') }}" class="text-sm font-semibold text-[#527863] transition hover:text-[#294b3b]">Add employee +</a>
                <a href="{{ route('admin.staff.index') }}" class="text-sm font-semibold text-[#527863] transition hover:text-[#294b3b]">Manage staff →</a>
            </div>
        </div>

        <div class="mt-5 overflow-hidden rounded-2xl border border-[#e4e9e3] bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-left text-sm">
                    <thead class="bg-[#f7f8f5] text-xs font-semibold uppercase tracking-wide text-[#748178]">
                        <tr>
                            <th scope="col" class="px-5 py-4">Employee</th>
                            <th scope="col" class="px-5 py-4">Role</th>
                            <th scope="col" class="px-5 py-4">Check in</th>
                            <th scope="col" class="px-5 py-4">Check out</th>
                            <th scope="col" class="px-5 py-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#edf0ec]">
                        @forelse ($staff as $staffMember)
                            @php($attendance = $staffMember->attendances->first())
                            <tr>
                                <td class="px-5 py-4">
                                    <a href="{{ route('admin.staff.show', $staffMember) }}" class="font-semibold text-[#315d48] transition hover:text-[#173b32] hover:underline">{{ $staffMember->name }}</a>
                                    <p class="mt-1 text-xs text-[#748178]">{{ $staffMember->email }}</p>
                                </td>
                                <td class="px-5 py-4 text-[#58645c]">{{ $staffMember->is_admin ? 'Administrator' : 'Employee' }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-[#58645c]">{{ $attendance?->check_in_at?->format('g:i A') ?? '—' }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-[#58645c]">{{ $attendance?->check_out_at?->format('g:i A') ?? '—' }}</td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $attendance?->check_out_at ? 'bg-[#edf5ee] text-[#315d48]' : ($attendance?->check_in_at ? 'bg-[#fbf2e8] text-[#8b5a2a]' : 'bg-[#f0f1ee] text-[#68736b]') }}">
                                        {{ $attendance?->check_out_at ? 'Complete' : ($attendance?->check_in_at ? 'In progress' : 'Not started') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-sm text-[#748178]">No staff accounts are registered yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-4">{{ $staff->links() }}</div>
    </section>
@endsection
