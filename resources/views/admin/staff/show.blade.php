@extends('layouts.attendance')

@section('title', 'Employee details')

@section('content')
    <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div>
            <a href="{{ route('admin.staff.index') }}" class="text-sm font-semibold text-[#527863] transition hover:text-[#294b3b]">← Back to staff</a>
            <div class="mt-6 flex items-center gap-4">
                @if ($staffMember->avatar_path)
                    <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($staffMember->avatar_path) }}" alt="{{ $staffMember->name }} profile picture" class="size-16 rounded-full object-cover">
                @else
                    <div aria-label="No profile picture uploaded" class="flex size-16 items-center justify-center rounded-full bg-[#edf5ee] text-xl font-semibold text-[#315d48]">
                        {{ mb_strtoupper(mb_substr($staffMember->name, 0, 1)) }}
                    </div>
                @endif
                <div>
                    <p class="text-sm font-medium text-[#748178]">Employee profile</p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-[-0.035em] text-[#18221e] sm:text-4xl">{{ $staffMember->name }}</h1>
                    <p class="mt-2 text-sm leading-6 text-[#748178]">{{ $staffMember->email }}</p>
                </div>
            </div>
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

    <section id="employee-salary" class="mt-8 max-w-2xl rounded-2xl border border-[#e4e9e3] bg-white p-6 shadow-sm sm:p-8" aria-labelledby="employee-salary-title">
        <h2 id="employee-salary-title" class="text-xl font-semibold tracking-tight text-[#18221e]">Monthly salary</h2>
        <p class="mt-1 text-sm text-[#748178]">Set the fixed monthly salary used on this employee's salary slip.</p>

        @if ($errors->has('monthly_salary'))
            <div role="alert" class="mt-4 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
                {{ $errors->first('monthly_salary') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.staff.salary', $staffMember) }}" class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-end">
            @csrf
            @method('PATCH')
            <div class="flex-1 space-y-2">
                <label for="monthly_salary" class="block text-sm font-medium text-[#303c35]">Monthly salary (INR)</label>
                <input id="monthly_salary" name="monthly_salary" type="number" value="{{ old('monthly_salary', $staffMember->monthly_salary) }}" min="0" max="9999999999.99" step="0.01" inputmode="decimal" required class="h-12 w-full rounded-lg border border-[#dce2dc] bg-white px-4 text-sm text-[#18221e] outline-none transition placeholder:text-[#a0aaa2] hover:border-[#aab8ac] focus:border-[#527863] focus:ring-4 focus:ring-[#527863]/10">
            </div>
            <button type="submit" class="flex h-12 items-center justify-center rounded-lg bg-[#244b3b] px-5 text-sm font-semibold text-white transition hover:bg-[#193d30] focus:outline-none focus:ring-4 focus:ring-[#527863]/20">Save salary</button>
        </form>
    </section>

    @include('salary-history.list', ['salaryHistories' => $salaryHistories])

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
