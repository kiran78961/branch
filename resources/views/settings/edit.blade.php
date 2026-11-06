@extends('layouts.attendance')

@section('title', 'Settings')

@section('content')
    <div>
        <p class="text-sm font-medium text-[#748178]">Your account</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-[-0.035em] text-[#18221e] sm:text-4xl">Settings</h1>
        <p class="mt-3 text-sm leading-6 text-[#748178]">Manage your appearance and attendance preferences.</p>
    </div>

    <div class="mt-8 grid gap-5">
        <section class="rounded-2xl border border-[#e4e9e3] bg-white p-6 shadow-sm sm:p-8" aria-labelledby="appearance-settings-heading">
            <h2 id="appearance-settings-heading" class="text-xl font-semibold tracking-tight text-[#18221e]">Appearance</h2>
            <p class="mt-2 text-sm text-[#748178]">Choose the display theme for this browser. Your choice is saved on this device.</p>
            <button type="button" data-theme-toggle aria-pressed="false" aria-label="Toggle dark mode" class="mt-5 inline-flex h-11 items-center justify-center gap-2 rounded-lg border border-[#dce2dc] px-4 text-sm font-medium text-[#303c35] transition hover:border-[#aab8ac] hover:bg-[#f7f8f5] focus:outline-none focus:ring-4 focus:ring-[#527863]/20">
                <span aria-hidden="true" data-theme-icon>☾</span>
                <span data-theme-label>Dark mode</span>
            </button>
        </section>

        <section class="rounded-2xl border border-[#e4e9e3] bg-white p-6 shadow-sm sm:p-8" aria-labelledby="attendance-settings-heading">
            <h2 id="attendance-settings-heading" class="text-xl font-semibold tracking-tight text-[#18221e]">Attendance preferences</h2>
            <p class="mt-2 text-sm text-[#748178]">Set your daily work target and planned lunch window. Lunch duration is excluded from your work timer.</p>

            <form method="POST" action="{{ route('settings.attendance.update') }}" class="mt-6 grid gap-5 sm:grid-cols-2">
                @csrf
                @method('PATCH')
                <div>
                    <label for="daily_work_hours" class="block text-sm font-medium text-[#303c35]">Daily working-hours target</label>
                    <input id="daily_work_hours" name="daily_work_hours" type="number" value="{{ old('daily_work_hours', $dailyWorkHours) }}" required min="1" max="24" step="1" class="mt-2 block h-11 w-full rounded-lg border border-[#dce2dc] bg-white px-3 text-sm text-[#18221e] focus:border-[#527863] focus:outline-none focus:ring-4 focus:ring-[#527863]/20">
                    @error('daily_work_hours')
                        <p role="alert" class="mt-2 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="lunch_start_time" class="block text-sm font-medium text-[#303c35]">Lunch starts</label>
                    <input id="lunch_start_time" name="lunch_start_time" type="time" value="{{ old('lunch_start_time', $lunchStartTime) }}" required class="mt-2 block h-11 w-full rounded-lg border border-[#dce2dc] bg-white px-3 text-sm text-[#18221e] focus:border-[#527863] focus:outline-none focus:ring-4 focus:ring-[#527863]/20">
                    @error('lunch_start_time')
                        <p role="alert" class="mt-2 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="lunch_end_time" class="block text-sm font-medium text-[#303c35]">Lunch ends</label>
                    <input id="lunch_end_time" name="lunch_end_time" type="time" value="{{ old('lunch_end_time', $lunchEndTime) }}" required class="mt-2 block h-11 w-full rounded-lg border border-[#dce2dc] bg-white px-3 text-sm text-[#18221e] focus:border-[#527863] focus:outline-none focus:ring-4 focus:ring-[#527863]/20">
                    @error('lunch_end_time')
                        <p role="alert" class="mt-2 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>
                <div class="sm:col-span-2">
                    <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-[#173b32] px-5 text-sm font-semibold text-white transition hover:bg-[#294b3b] focus:outline-none focus:ring-4 focus:ring-[#527863]/20">
                        Save attendance preferences
                    </button>
                </div>
            </form>
        </section>

        <section class="rounded-2xl border border-[#e4e9e3] bg-white p-6 shadow-sm sm:p-8" aria-labelledby="company-attendance-settings-heading">
            <h2 id="company-attendance-settings-heading" class="text-xl font-semibold tracking-tight text-[#18221e]">Company attendance</h2>
            <p class="mt-2 text-sm text-[#748178]">Company workday starts at {{ \Illuminate\Support\Carbon::createFromFormat('H:i', $companyStartTime, 'Asia/Kolkata')->format('g:i A') }}. Check-ins after this time are marked late.</p>
            @if ($isAdmin)
                <form method="POST" action="{{ route('settings.company-attendance.update') }}" class="mt-5 flex flex-wrap items-end gap-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label for="workday_start_time" class="block text-sm font-medium text-[#303c35]">Company start time</label>
                        <input id="workday_start_time" name="workday_start_time" type="time" value="{{ old('workday_start_time', $companyStartTime) }}" required class="mt-2 block h-11 w-full rounded-lg border border-[#dce2dc] bg-white px-3 text-sm text-[#18221e] focus:border-[#527863] focus:outline-none focus:ring-4 focus:ring-[#527863]/20">
                        @error('workday_start_time')
                            <p role="alert" class="mt-2 text-sm text-red-700">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-[#173b32] px-5 text-sm font-semibold text-white transition hover:bg-[#294b3b] focus:outline-none focus:ring-4 focus:ring-[#527863]/20">
                        Save company start time
                    </button>
                </form>
            @else
                <p class="mt-4 text-sm font-medium text-[#303c35]">Company start time: {{ \Illuminate\Support\Carbon::createFromFormat('H:i', $companyStartTime, 'Asia/Kolkata')->format('g:i A') }}</p>
            @endif
        </section>

        <section class="rounded-2xl border border-[#e4e9e3] bg-white p-6 shadow-sm sm:p-8" aria-labelledby="account-settings-heading">
            <h2 id="account-settings-heading" class="text-xl font-semibold tracking-tight text-[#18221e]">Account</h2>
            <p class="mt-2 text-sm text-[#748178]">Sign out of your account on this device.</p>
            <form method="POST" action="{{ route('logout') }}" class="mt-5">
                @csrf
                <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg border border-[#dce2dc] px-5 text-sm font-medium text-[#303c35] transition hover:border-[#aab8ac] hover:bg-[#f7f8f5] focus:outline-none focus:ring-4 focus:ring-[#527863]/20">
                    Sign out
                </button>
            </form>
        </section>
    </div>
@endsection
