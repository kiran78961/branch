@extends('layouts.attendance')

@section('title', 'Attendance history')

@section('content')
    <div>
        <p class="text-sm font-medium text-[#748178]">Your records</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-[-0.035em] text-[#18221e] sm:text-4xl">Attendance history</h1>
        <p class="mt-2 text-sm leading-6 text-[#748178]">Review your check-in and check-out records or filter by a calendar date.</p>
    </div>

    <form method="GET" action="{{ route('attendance.history') }}" class="mt-8 flex flex-wrap items-end gap-3 rounded-2xl border border-[#e4e9e3] bg-white p-5 shadow-sm">
        <div class="space-y-2">
            <label for="date" class="block text-xs font-semibold uppercase tracking-wide text-[#748178]">Filter by date</label>
            <input id="date" name="date" type="date" value="{{ $selectedDate }}" class="h-11 rounded-lg border border-[#dce2dc] bg-white px-3 text-sm text-[#18221e] outline-none focus:border-[#527863] focus:ring-4 focus:ring-[#527863]/10">
        </div>
        <button type="submit" class="h-11 rounded-lg bg-[#244b3b] px-4 text-sm font-semibold text-white transition hover:bg-[#193d30] focus:outline-none focus:ring-4 focus:ring-[#527863]/20">Filter</button>
        @if ($selectedDate !== '')
            <a href="{{ route('attendance.history') }}" class="inline-flex h-11 items-center rounded-lg border border-[#dce2dc] px-4 text-sm font-semibold text-[#58645c] transition hover:bg-[#f7f8f5]">Clear filter</a>
        @endif
    </form>

    @if ($errors->has('date'))
        <div role="alert" class="mt-5 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
            Choose a valid calendar date.
        </div>
    @endif

    <section class="mt-8" aria-labelledby="history-records-title">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 id="history-records-title" class="text-xl font-semibold tracking-tight text-[#18221e]">
                    {{ $selectedDate !== '' ? \Illuminate\Support\Carbon::parse($selectedDate)->format('F j, Y') : 'All attendance records' }}
                </h2>
                <p class="mt-1 text-sm text-[#748178]">{{ $attendanceRecords->total() }} {{ \Illuminate\Support\Str::plural('record', $attendanceRecords->total()) }}</p>
            </div>
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
                        @forelse ($attendanceRecords as $attendance)
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
                                <td colspan="4" class="px-5 py-10 text-center text-sm text-[#748178]">
                                    {{ $selectedDate !== '' ? 'No attendance records for this date.' : 'Your attendance history will appear here after your first check-in.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-4">{{ $attendanceRecords->links() }}</div>
    </section>
@endsection
