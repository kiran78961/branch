@extends('layouts.attendance')

@section('title', 'Employee details')

@section('content')
    <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div>
            <a href="{{ route('admin.staff.index') }}" class="text-sm font-semibold text-[#527863] transition hover:text-[#294b3b]">← Back to staff</a>
            <p class="mt-6 text-sm font-medium text-[#748178]">Employee profile</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-[-0.035em] text-[#18221e] sm:text-4xl">{{ $staffMember->name }}</h1>
            <p class="mt-2 text-sm leading-6 text-[#748178]">{{ $staffMember->email }}</p>
        </div>
        @if (! $staffMember->is(auth()->user()))
            <form method="POST" action="{{ route('admin.staff.role', $staffMember) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="is_admin" value="{{ $staffMember->is_admin ? '0' : '1' }}">
                <button type="submit" class="rounded-lg border border-[#dce2dc] px-4 py-2.5 text-sm font-semibold text-[#315d48] transition hover:border-[#aab8ac] hover:bg-white">
                    {{ $staffMember->is_admin ? 'Remove admin access' : 'Make administrator' }}
                </button>
            </form>
        @endif
    </div>

    <section class="mt-8 grid gap-4 sm:grid-cols-3" aria-label="Employee details">
        <div class="rounded-2xl border border-[#e4e9e3] bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-[#748178]">Account role</p>
            <p class="mt-2 text-lg font-semibold text-[#18221e]">{{ $staffMember->is_admin ? 'Administrator' : 'Employee' }}</p>
        </div>
        <div class="rounded-2xl border border-[#e4e9e3] bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-[#748178]">Joined</p>
            <p class="mt-2 text-lg font-semibold text-[#18221e]">{{ $staffMember->created_at->format('M j, Y') }}</p>
        </div>
        <div class="rounded-2xl border border-[#e4e9e3] bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-[#748178]">Attendance records</p>
            <p class="mt-2 text-lg font-semibold text-[#18221e]">{{ $attendances->total() }}</p>
        </div>
    </section>

    <section class="mt-9" aria-labelledby="employee-attendance-title">
        <div>
            <h2 id="employee-attendance-title" class="text-xl font-semibold tracking-tight text-[#18221e]">Attendance history</h2>
            <p class="mt-1 text-sm text-[#748178]">Check-in and check-out records for this employee.</p>
        </div>

        <div class="mt-5 overflow-hidden rounded-2xl border border-[#e4e9e3] bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[560px] text-left text-sm">
                    <thead class="bg-[#f7f8f5] text-xs font-semibold uppercase tracking-wide text-[#748178]">
                        <tr>
                            <th scope="col" class="px-5 py-4">Date</th>
                            <th scope="col" class="px-5 py-4">Check in</th>
                            <th scope="col" class="px-5 py-4">Check out</th>
                            <th scope="col" class="px-5 py-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#edf0ec]">
                        @forelse ($attendances as $attendance)
                            <tr>
                                <td class="whitespace-nowrap px-5 py-4 font-medium text-[#303c35]">{{ $attendance->attendance_date->format('D, M j, Y') }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-[#58645c]">{{ $attendance->check_in_at?->format('g:i A') ?? '—' }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-[#58645c]">{{ $attendance->check_out_at?->format('g:i A') ?? '—' }}</td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $attendance->check_out_at ? 'bg-[#edf5ee] text-[#315d48]' : 'bg-[#fbf2e8] text-[#8b5a2a]' }}">
                                        {{ $attendance->check_out_at ? 'Complete' : 'In progress' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center text-sm text-[#748178]">No attendance records for this employee yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-4">{{ $attendances->links() }}</div>
    </section>
@endsection
