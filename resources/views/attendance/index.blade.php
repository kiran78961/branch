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

    @if ($errors->has('attendance'))
        <div role="alert" class="mt-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
            {{ $errors->first('attendance') }}
        </div>
    @endif

    <section class="mt-8 grid gap-5 lg:grid-cols-[1.2fr_0.8fr]" aria-label="Today's attendance">
        <div class="rounded-2xl bg-[#173b32] p-6 text-white shadow-sm sm:p-8">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-[#c5d6c6]">Today’s status</p>
                    <h2 class="mt-2 text-2xl font-semibold tracking-tight">
                        @if ($todayAttendance?->check_out_at)
                            Day complete
                        @elseif ($todayAttendance?->check_in_at)
                            You’re at work
                        @else
                            Not checked in
                        @endif
                    </h2>
                </div>
                <span class="rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold text-[#dce8da]">
                    {{ $todayAttendance?->check_out_at ? 'Complete' : ($todayAttendance?->check_in_at ? 'In progress' : 'Not started') }}
                </span>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl bg-white/10 p-4 ring-1 ring-white/10">
                    <p class="text-xs font-medium uppercase tracking-wide text-white/60">Check in</p>
                    <p class="mt-2 text-xl font-semibold">{{ $todayAttendance?->check_in_at?->format('g:i A') ?? '—' }}</p>
                </div>
                <div class="rounded-xl bg-white/10 p-4 ring-1 ring-white/10">
                    <p class="text-xs font-medium uppercase tracking-wide text-white/60">Check out</p>
                    <p class="mt-2 text-xl font-semibold">{{ $todayAttendance?->check_out_at?->format('g:i A') ?? '—' }}</p>
                </div>
            </div>

            @if (! $todayAttendance?->check_in_at)
                <form method="POST" action="{{ route('attendance.check-in') }}" class="mt-6">
                    @csrf
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

        <div class="rounded-2xl border border-[#e4e9e3] bg-white p-6 shadow-sm sm:p-8">
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
