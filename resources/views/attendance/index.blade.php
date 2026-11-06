@extends('layouts.attendance')

@section('title', 'My attendance')

@section('content')
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-medium text-[#748178]">{{ today()->format('l, F j, Y') }}</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-[-0.035em] text-[#18221e] sm:text-4xl">Your workday</h1>
            <p class="mt-2 text-sm leading-6 text-[#748178]">Keep your office attendance up to date.</p>
        </div>
        <div class="rounded-xl border border-[#e4e9e3] bg-white px-4 py-3 text-sm text-[#58645c]">
            Signed in as <span class="font-semibold text-[#303c35]">{{ auth()->user()->name }}</span>
        </div>
    </div>

    <section class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Today's work status summary">
        <article data-attendance-summary-card class="branch-card-enter branch-card-hover rounded-2xl border border-[#e4e9e3] bg-gradient-to-br from-white via-white to-emerald-50 p-5 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-[#748178]">Present</p>
                    <p class="mt-3 text-2xl font-semibold tracking-tight text-[#18221e]" data-attendance-summary-value="present">{{ $presentCount }}</p>
                    <p class="mt-1 text-xs text-[#748178]">On-site today</p>
                </div>
                <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-emerald-500 text-white shadow-sm" aria-hidden="true">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="9"></circle>
                        <path d="m8.5 12 2.25 2.25L15.5 9.5"></path>
                    </svg>
                </span>
            </div>
        </article>
        <article data-attendance-summary-card class="branch-card-enter branch-card-enter--delayed branch-card-hover rounded-2xl border border-[#e4e9e3] bg-gradient-to-br from-white via-white to-blue-50 p-5 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-[#748178]">WFH</p>
                    <p class="mt-3 text-2xl font-semibold tracking-tight text-[#18221e]" data-attendance-summary-value="wfh">{{ $wfhCount }}</p>
                    <p class="mt-1 text-xs text-[#748178]">Working remote</p>
                </div>
                <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-blue-500 text-white shadow-sm" aria-hidden="true">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="m3 10 9-7 9 7"></path>
                        <path d="M5 9v11h14V9M9 20v-6h6v6"></path>
                    </svg>
                </span>
            </div>
        </article>
        <article data-attendance-summary-card class="branch-card-enter branch-card-hover rounded-2xl border border-[#e4e9e3] bg-gradient-to-br from-white via-white to-orange-50 p-5 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-[#748178]">Leave</p>
                    <p class="mt-3 text-2xl font-semibold tracking-tight text-[#18221e]" data-attendance-summary-value="leave">{{ $leaveCount }}</p>
                    <p class="mt-1 text-xs text-[#748178]">Approved leaves</p>
                </div>
                <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-orange-500 text-white shadow-sm" aria-hidden="true">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="9"></circle>
                        <path d="M8 12h8"></path>
                    </svg>
                </span>
            </div>
        </article>
        <article data-attendance-summary-card class="branch-card-enter branch-card-enter--delayed branch-card-hover rounded-2xl border border-[#e4e9e3] bg-gradient-to-br from-white via-white to-rose-50 p-5 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-[#748178]">Absent</p>
                    <p class="mt-3 text-2xl font-semibold tracking-tight text-[#18221e]" data-attendance-summary-value="absent">{{ $absentCount }}</p>
                    <p class="mt-1 text-xs text-[#748178]">Unplanned · weekdays</p>
                </div>
                <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-rose-500 text-white shadow-sm" aria-hidden="true">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="9"></circle>
                        <path d="m9 9 6 6m0-6-6 6"></path>
                    </svg>
                </span>
            </div>
        </article>
    </section>

    @if ($errors->has('attendance'))
        <div role="alert" class="mt-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
            {{ $errors->first('attendance') }}
        </div>
    @endif

    <section class="mt-8 grid gap-5 lg:grid-cols-[1.2fr_0.8fr]" aria-label="Today's attendance">
        <div class="branch-card-enter branch-card-hover rounded-2xl bg-[#173b32] p-6 text-white shadow-sm sm:p-8">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-[#c5d6c6]">Today’s status</p>
                    <h2 class="mt-2 text-2xl font-semibold tracking-tight">
                        @if ($todayAttendance?->check_out_at)
                            Day complete
                        @elseif ($todayAttendance?->check_in_at)
                            {{ $todayAttendance->work_location === 'wfh' ? 'Working remotely' : 'You’re at work' }}
                        @else
                            Not checked in
                        @endif
                    </h2>
                </div>
                <span class="rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold text-[#dce8da]">
                    {{ $todayAttendance?->is_late && $todayAttendance->check_in_at ? 'Late — Employee arrived late' : ($todayAttendance?->check_out_at ? 'Complete' : ($todayAttendance?->check_in_at ? 'In progress' : 'Not started')) }}
                </span>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl bg-white/10 p-4 ring-1 ring-white/10">
                    <p class="text-xs font-medium uppercase tracking-wide text-white/60">Check in</p>
                    <p class="mt-2 text-xl font-semibold">
                        @if ($todayAttendance?->check_in_at)
                            {{ $todayAttendance->check_in_at->setTimezone('Asia/Kolkata')->format('g:i A') }}
                        @else
                            —
                        @endif
                    </p>
                </div>
                <div class="rounded-xl bg-white/10 p-4 ring-1 ring-white/10">
                    <p class="text-xs font-medium uppercase tracking-wide text-white/60">Check out</p>
                    <p class="mt-2 text-xl font-semibold">
                        @if ($todayAttendance?->check_out_at)
                            {{ $todayAttendance->check_out_at->setTimezone('Asia/Kolkata')->format('g:i A') }}
                        @else
                            —
                        @endif
                    </p>
                </div>
                <div class="rounded-xl bg-white/10 p-4 ring-1 ring-white/10">
                    <p class="text-xs font-medium uppercase tracking-wide text-white/60">Time worked</p>
                    <time
                        data-work-timer
                        data-check-in-at="{{ $todayAttendance?->check_in_at?->toIso8601String() ?? '' }}"
                        data-check-out-at="{{ $todayAttendance?->check_out_at?->toIso8601String() ?? '' }}"
                        data-lunch-started-at="{{ $todayAttendance?->lunch_started_at?->toIso8601String() ?? '' }}"
                        data-lunch-ended-at="{{ $todayAttendance?->lunch_ended_at?->toIso8601String() ?? '' }}"
                        class="mt-2 block text-xl font-semibold tabular-nums"
                    >{{ $todayAttendance?->check_in_at ? '00:00:00' : 'Not started' }}</time>
                </div>
                <div class="rounded-xl bg-white/10 p-4 ring-1 ring-white/10">
                    <p class="text-xs font-medium uppercase tracking-wide text-white/60">Lunch break</p>
                    <time
                        data-lunch-timer
                        data-lunch-started-at="{{ $todayAttendance?->lunch_started_at?->toIso8601String() ?? '' }}"
                        data-lunch-ended-at="{{ $todayAttendance?->lunch_ended_at?->toIso8601String() ?? '' }}"
                        class="mt-2 block text-xl font-semibold tabular-nums"
                    >{{ $todayAttendance?->lunch_started_at ? '00:00:00' : 'Not started' }}</time>
                    <p class="mt-1 text-xs text-white/60">Planned window: {{ $lunchSchedule }}</p>
                </div>
            </div>
            <div class="mt-5 rounded-xl bg-white/10 p-4 ring-1 ring-white/10" data-work-progress data-work-target-seconds="{{ $dailyWorkTargetSeconds }}" data-work-target-hours="{{ $dailyWorkHours }}">
                <div class="flex flex-wrap items-center justify-between gap-2 text-sm">
                    <p class="font-medium">Daily working-hours target</p>
                    <p data-work-progress-label>0% of {{ $dailyWorkHours }}-hour target</p>
                </div>
                <div
                    class="mt-3 h-2 overflow-hidden rounded-full bg-white/20"
                    role="progressbar"
                    aria-label="Daily working-hours progress"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    aria-valuenow="0"
                    data-work-progress-bar
                >
                    <div class="h-full rounded-full bg-[#d49d68] transition-[width] duration-500" style="width: 0%"></div>
                </div>
                <p class="mt-2 text-xs text-white/70" data-work-progress-remaining>
                    {{ $todayAttendance?->check_in_at ? sprintf('%02d', $dailyWorkHours).':00:00 remaining' : 'Check in to start progress' }}
                </p>
            </div>

            @if ($todayAttendance?->check_in_at && ! $todayAttendance->check_out_at)
                @if (! $todayAttendance->lunch_started_at)
                    <form method="POST" action="{{ route('attendance.lunch.start') }}" class="mt-6">
                        @csrf
                        <button type="submit" class="flex h-12 w-full items-center justify-center rounded-lg border border-white/30 px-5 text-sm font-semibold text-white transition hover:bg-white/10 focus:outline-none focus:ring-4 focus:ring-white/20 sm:w-auto">
                            Start lunch
                        </button>
                    </form>
                @elseif (! $todayAttendance->lunch_ended_at)
                    <form method="POST" action="{{ route('attendance.lunch.end') }}" class="mt-6">
                        @csrf
                        <button type="submit" class="flex h-12 w-full items-center justify-center rounded-lg border border-white/30 px-5 text-sm font-semibold text-white transition hover:bg-white/10 focus:outline-none focus:ring-4 focus:ring-white/20 sm:w-auto">
                            End lunch
                        </button>
                    </form>
                @else
                    <p class="mt-6 text-sm text-[#dce8da]">Your lunch break is complete.</p>
                @endif
            @elseif (! $todayAttendance?->check_in_at)
                <p class="mt-6 text-sm text-[#dce8da]">Check in to start your lunch timer.</p>
            @elseif ($todayAttendance->lunch_ended_at)
                <p class="mt-6 text-sm text-[#dce8da]">Your lunch break is complete.</p>
            @else
                <p class="mt-6 text-sm text-[#dce8da]">No lunch break was recorded today.</p>
            @endif

            @if (! $todayAttendance?->check_in_at)
                <form method="POST" action="{{ route('attendance.check-in') }}" class="mt-6">
                    @csrf
                    <fieldset>
                        <legend class="text-sm font-semibold text-[#dce8da]">Where are you working today?</legend>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2 sm:max-w-md">
                            <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-white/30 px-4 py-3 text-sm font-medium text-white transition has-[:checked]:border-[#d49d68] has-[:checked]:bg-white/10">
                                <input type="radio" name="work_location" value="office" @checked(old('work_location', 'office') === 'office') class="accent-[#d49d68]">
                                Office
                            </label>
                            <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-white/30 px-4 py-3 text-sm font-medium text-white transition has-[:checked]:border-[#d49d68] has-[:checked]:bg-white/10">
                                <input type="radio" name="work_location" value="wfh" @checked(old('work_location') === 'wfh') class="accent-[#d49d68]">
                                Work from home
                            </label>
                        </div>
                        @error('work_location')
                            <p role="alert" class="mt-2 text-sm text-red-200">{{ $message }}</p>
                        @enderror
                    </fieldset>
                    <button type="submit" class="flex h-12 w-full items-center justify-center rounded-lg bg-[#d49d68] px-5 text-sm font-semibold text-[#18221e] transition hover:bg-[#e2b98c] focus:outline-none focus:ring-4 focus:ring-white/20 sm:w-auto">
                        Check in
                    </button>
                </form>
            @elseif (! $todayAttendance->check_out_at)
                <form method="POST" action="{{ route('attendance.check-out') }}" class="mt-6">
                    @csrf
                    <button type="submit" class="flex h-12 w-full items-center justify-center rounded-lg bg-white px-5 text-sm font-semibold text-[#173b32] transition hover:bg-[#edf5ee] focus:outline-none focus:ring-4 focus:ring-white/20 sm:w-auto">
                        Check out
                    </button>
                </form>
            @else
                <p class="mt-6 text-sm text-[#dce8da]">Your attendance for today is complete.</p>
            @endif
        </div>

        <div class="branch-card-enter branch-card-enter--delayed branch-card-hover rounded-2xl border border-[#e4e9e3] bg-white p-6 shadow-sm sm:p-8">
            <p class="text-sm font-medium text-[#748178]">A quick reminder</p>
            <h2 class="mt-2 text-xl font-semibold tracking-tight text-[#18221e]">One check-in, one check-out</h2>
            <p class="mt-3 text-sm leading-6 text-[#748178]">
                Record your arrival when you start and your departure when you finish. Your times are saved to your attendance history.
            </p>
            <div class="mt-6 flex items-center gap-3 rounded-xl bg-[#f7f8f5] p-4">
                <span class="grid size-10 shrink-0 place-items-center rounded-full bg-[#edf5ee] text-[#315d48]" aria-hidden="true">✓</span>
                <p class="text-sm leading-5 text-[#58645c]">Your attendance is visible only to you and authorized administrators.</p>
            </div>
        </div>
    </section>

@endsection
