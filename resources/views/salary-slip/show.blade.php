@extends('layouts.attendance')

@section('title', 'Salary slip')

@section('content')
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-medium text-[#748178]">{{ $salaryMonth->format('F Y') }}</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-[-0.035em] text-[#18221e] sm:text-4xl">Your salary</h1>
            <p class="mt-2 text-sm leading-6 text-[#748178]">Review your monthly pay and salary history.</p>
        </div>
        <div class="rounded-xl border border-[#e4e9e3] bg-white px-4 py-3 text-sm text-[#58645c]">
            Signed in as <span class="font-semibold text-[#303c35]">{{ $user->name }}</span>
        </div>
    </div>

    @if ($salaryNotifications->isNotEmpty())
        <section class="mt-8 space-y-3" aria-label="Notifications">
            @foreach ($salaryNotifications as $notification)
                <div role="status" class="branch-card-enter flex flex-col justify-between gap-3 rounded-xl border border-[#cbdccf] bg-[#edf5ee] px-5 py-4 text-sm text-[#315d48] sm:flex-row sm:items-center">
                    <a href="{{ $notification->data['url'] }}" class="font-medium underline-offset-4 transition hover:underline">{{ $notification->data['message'] }}</a>
                    <form method="POST" action="{{ route('salary-notifications.read', $notification) }}">
                        @csrf
                        <button type="submit" class="shrink-0 rounded-lg border border-[#aab8ac] px-3 py-2 text-xs font-semibold transition hover:bg-white focus:outline-none focus:ring-4 focus:ring-[#527863]/20">Mark as read</button>
                    </form>
                </div>
            @endforeach
            <div>{{ $salaryNotifications->links() }}</div>
        </section>
    @endif

    <section class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-3" aria-label="Salary summary">
        <article data-attendance-summary-card class="branch-card-enter branch-card-hover rounded-2xl border border-[#e4e9e3] bg-gradient-to-br from-white via-white to-emerald-50 p-5 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-[#748178]">Monthly salary</p>
                    <p class="mt-3 text-2xl font-semibold tracking-tight text-[#18221e]">
                        {{ $user->monthly_salary === null ? 'Not set' : '₹'.number_format((float) $user->monthly_salary, 2) }}
                    </p>
                    <p class="mt-1 text-xs text-[#748178]">Fixed monthly pay</p>
                </div>
                <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-emerald-500 text-white shadow-sm" aria-hidden="true">
                    <span class="text-lg font-semibold">₹</span>
                </span>
            </div>
        </article>
        <article data-attendance-summary-card class="branch-card-enter branch-card-enter--delayed branch-card-hover rounded-2xl border border-[#e4e9e3] bg-gradient-to-br from-white via-white to-blue-50 p-5 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-[#748178]">Pay period</p>
                    <p class="mt-3 text-2xl font-semibold tracking-tight text-[#18221e]">{{ $salaryMonth->format('F') }}</p>
                    <p class="mt-1 text-xs text-[#748178]">{{ $salaryMonth->format('Y') }}</p>
                </div>
                <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-blue-500 text-white shadow-sm" aria-hidden="true">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="5" width="18" height="16" rx="2"></rect>
                        <path d="M16 3v4M8 3v4M3 11h18"></path>
                    </svg>
                </span>
            </div>
        </article>
        <article data-attendance-summary-card class="branch-card-enter branch-card-hover rounded-2xl border border-[#e4e9e3] bg-gradient-to-br from-white via-white to-orange-50 p-5 shadow-sm sm:col-span-2 xl:col-span-1">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-[#748178]">Salary records</p>
                    <p class="mt-3 text-2xl font-semibold tracking-tight text-[#18221e]">{{ $salaryHistories->total() }}</p>
                    <p class="mt-1 text-xs text-[#748178]">Assignments and changes</p>
                </div>
                <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-orange-500 text-white shadow-sm" aria-hidden="true">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"></path>
                    </svg>
                </span>
            </div>
        </article>
    </section>

    <section class="branch-card-enter branch-card-hover mt-8 overflow-hidden rounded-2xl bg-[#173b32] p-6 text-white shadow-sm sm:p-8" aria-label="Salary slip details">
        <div class="flex flex-col justify-between gap-6 sm:flex-row sm:items-center">
            <div>
                <p class="text-sm font-medium text-[#c5d6c6]">Your monthly pay</p>
                @if ($user->monthly_salary === null)
                    <h2 class="mt-2 text-2xl font-semibold tracking-tight">Salary not set yet</h2>
                    <p class="mt-2 max-w-xl text-sm leading-6 text-white/70">Your monthly salary has not been set yet. Please contact an administrator to configure it.</p>
                @else
                    <h2 class="mt-2 text-3xl font-semibold tracking-tight sm:text-4xl">₹{{ number_format((float) $user->monthly_salary, 2) }}</h2>
                    <p class="mt-2 text-sm text-white/70">Pay period: {{ $salaryMonth->format('F Y') }}</p>
                @endif
            </div>
            @if ($user->monthly_salary !== null)
                <a href="{{ route('salary-slip.download') }}" class="inline-flex h-12 shrink-0 items-center justify-center gap-2 rounded-xl bg-white px-5 text-sm font-semibold text-[#173b32] shadow-sm transition hover:-translate-y-0.5 hover:bg-[#edf5ee] focus:outline-none focus:ring-4 focus:ring-white/30">
                    <span aria-hidden="true">↓</span>
                    Download PDF
                </a>
            @endif
        </div>
        @if ($user->monthly_salary !== null)
            <p class="mt-6 border-t border-white/15 pt-4 text-xs leading-5 text-white/60">This slip shows your fixed monthly salary. Allowances and deductions are not managed in this app.</p>
        @endif
    </section>

    @include('salary-history.list', ['salaryHistories' => $salaryHistories])
@endsection
