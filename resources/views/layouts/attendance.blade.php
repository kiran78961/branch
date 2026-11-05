<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <title>@yield('title') · Branch</title>
    <script>
        const savedTheme = localStorage.getItem('branch-theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

        if (savedTheme === 'dark' || (savedTheme === null && prefersDark)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f7f8f5] text-[#18221e] antialiased">
    <div class="min-h-screen lg:grid lg:grid-cols-[17rem_minmax(0,1fr)]">
        <aside class="border-b border-[#e4e9e3] bg-white lg:sticky lg:top-0 lg:flex lg:h-screen lg:flex-col lg:border-b-0 lg:border-r">
            <div class="flex flex-wrap items-center justify-between gap-4 px-5 py-4 sm:px-8 lg:block lg:px-6 lg:py-7">
                <a href="{{ route('attendance.index') }}" class="inline-flex items-center gap-3 text-[#173b32] no-underline">
                    <span class="grid size-10 place-items-center rounded-xl bg-[#173b32] text-sm font-semibold text-white">B</span>
                    <span class="branch-wordmark text-xl font-semibold tracking-tight">branch</span>
                </a>
                <span class="rounded-full bg-[#edf5ee] px-3 py-1 text-xs font-semibold text-[#315d48] lg:mt-6 lg:inline-flex">
                    {{ auth()->user()->is_admin ? 'Administrator' : 'Employee' }}
                </span>
            </div>

            <nav class="flex flex-wrap gap-2 px-5 pb-4 text-sm sm:px-8 lg:flex-1 lg:flex-col lg:gap-1 lg:px-4 lg:py-2" aria-label="Sidebar navigation">
                <p class="hidden px-3 pb-2 text-xs font-semibold uppercase tracking-[0.16em] text-[#98a199] lg:block">Workspace</p>
                <a href="{{ route('attendance.index') }}" @class([
                    'inline-flex items-center gap-3 rounded-lg px-3 py-2.5 font-medium transition',
                    'bg-[#edf5ee] text-[#315d48]' => request()->routeIs('attendance.index'),
                    'text-[#58645c] hover:bg-[#f7f8f5] hover:text-[#294b3b]' => ! request()->routeIs('attendance.index'),
                ]) @if (request()->routeIs('attendance.index')) aria-current="page" @endif>
                    <span class="grid size-5 place-items-center" aria-hidden="true">◷</span>
                    My attendance
                </a>
                <a href="{{ route('attendance.history') }}" @class([
                    'inline-flex items-center gap-3 rounded-lg px-3 py-2.5 font-medium transition',
                    'bg-[#edf5ee] text-[#315d48]' => request()->routeIs('attendance.history'),
                    'text-[#58645c] hover:bg-[#f7f8f5] hover:text-[#294b3b]' => ! request()->routeIs('attendance.history'),
                ]) @if (request()->routeIs('attendance.history')) aria-current="page" @endif>
                    <span class="grid size-5 place-items-center" aria-hidden="true">▤</span>
                    Attendance history
                </a>
                @if (! auth()->user()->is_admin)
                    <a href="{{ route('leave-requests.index') }}" @class([
                        'inline-flex items-center gap-3 rounded-lg px-3 py-2.5 font-medium transition',
                        'bg-[#edf5ee] text-[#315d48]' => request()->routeIs('leave-requests.*'),
                        'text-[#58645c] hover:bg-[#f7f8f5] hover:text-[#294b3b]' => ! request()->routeIs('leave-requests.*'),
                    ]) @if (request()->routeIs('leave-requests.*')) aria-current="page" @endif>
                        <span class="grid size-5 place-items-center" aria-hidden="true">▣</span>
                        Leave requests
                    </a>
                @endif
                @if (auth()->user()->is_admin)
                    <p class="hidden px-3 pb-2 pt-5 text-xs font-semibold uppercase tracking-[0.16em] text-[#98a199] lg:block">Administration</p>
                    <a href="{{ route('admin.attendance.index') }}" @class([
                        'inline-flex items-center gap-3 rounded-lg px-3 py-2.5 font-medium transition',
                        'bg-[#edf5ee] text-[#315d48]' => request()->routeIs('admin.attendance.*'),
                        'text-[#58645c] hover:bg-[#f7f8f5] hover:text-[#294b3b]' => ! request()->routeIs('admin.attendance.*'),
                    ]) @if (request()->routeIs('admin.attendance.*')) aria-current="page" @endif>
                        <span class="grid size-5 place-items-center" aria-hidden="true">▥</span>
                        Team report
                    </a>
                    <a href="{{ route('admin.staff.index') }}" @class([
                        'inline-flex items-center gap-3 rounded-lg px-3 py-2.5 font-medium transition',
                        'bg-[#edf5ee] text-[#315d48]' => request()->routeIs('admin.staff.index', 'admin.staff.show'),
                        'text-[#58645c] hover:bg-[#f7f8f5] hover:text-[#294b3b]' => ! request()->routeIs('admin.staff.index', 'admin.staff.show'),
                    ]) @if (request()->routeIs('admin.staff.index', 'admin.staff.show')) aria-current="page" @endif>
                        <span class="grid size-5 place-items-center" aria-hidden="true">♙</span>
                        Staff
                    </a>
                    <a href="{{ route('admin.leave-requests.index') }}" @class([
                        'inline-flex items-center gap-3 rounded-lg px-3 py-2.5 font-medium transition',
                        'bg-[#edf5ee] text-[#315d48]' => request()->routeIs('admin.leave-requests.*'),
                        'text-[#58645c] hover:bg-[#f7f8f5] hover:text-[#294b3b]' => ! request()->routeIs('admin.leave-requests.*'),
                    ]) @if (request()->routeIs('admin.leave-requests.*')) aria-current="page" @endif>
                        <span class="grid size-5 place-items-center" aria-hidden="true">▣</span>
                        Leave requests
                    </a>
                    <a href="{{ route('admin.staff.create') }}" @class([
                        'inline-flex items-center gap-3 rounded-lg px-3 py-2.5 font-medium transition',
                        'bg-[#edf5ee] text-[#315d48]' => request()->routeIs('admin.staff.create'),
                        'text-[#58645c] hover:bg-[#f7f8f5] hover:text-[#294b3b]' => ! request()->routeIs('admin.staff.create'),
                    ]) @if (request()->routeIs('admin.staff.create')) aria-current="page" @endif>
                        <span class="grid size-5 place-items-center" aria-hidden="true">＋</span>
                        Add employee
                    </a>
                @endif
            </nav>

            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-[#edf0ec] px-5 py-4 sm:px-8 lg:flex-col lg:items-stretch lg:px-4">
                <div class="hidden px-3 pb-1 lg:block">
                    <p class="truncate text-sm font-semibold text-[#303c35]">{{ auth()->user()->name }}</p>
                    <p class="truncate text-xs text-[#748178]">{{ auth()->user()->email }}</p>
                </div>
                <button type="button" data-theme-toggle aria-pressed="false" aria-label="Toggle dark mode" class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-[#dce2dc] px-3 text-sm font-medium text-[#303c35] transition hover:border-[#aab8ac] hover:bg-[#f7f8f5] focus:outline-none focus:ring-4 focus:ring-[#527863]/20">
                    <span aria-hidden="true" data-theme-icon>☾</span>
                    <span data-theme-label>Dark mode</span>
                </button>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-lg border border-[#dce2dc] px-3 py-2 text-sm font-medium text-[#303c35] transition hover:border-[#aab8ac] hover:bg-[#f7f8f5]">
                        Sign out
                    </button>
                </form>
            </div>
        </aside>

        <main class="mx-auto w-full max-w-7xl px-5 py-8 sm:px-8 sm:py-12">
            @if (session('status'))
                <div role="status" class="mb-6 rounded-xl border border-[#cbdccf] bg-[#edf5ee] px-5 py-4 text-sm text-[#315d48]">
                    {{ session('status') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
