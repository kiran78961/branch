<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Verify your email · Branch</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="grid min-h-screen place-items-center bg-[#f7f8f5] px-6 py-12 text-[#18221e] antialiased">
    <main class="w-full max-w-lg rounded-2xl bg-white p-8 shadow-sm ring-1 ring-[#dce2dc] sm:p-10">
        <a href="/" class="mb-8 inline-flex items-center gap-3 text-[#173b32] no-underline" aria-label="Branch home">
            <span class="grid size-10 place-items-center rounded-xl bg-[#173b32] text-white">
                <svg class="size-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M6 19V9m0 5 5-5m-5 5-3-3m9 9V5m0 8 6-6m-6 6 5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="12" cy="5" r="1.5" fill="#d49d68"/>
                </svg>
            </span>
            <span class="text-xl font-semibold tracking-tight">branch</span>
        </a>

        <div class="space-y-4">
            <p class="text-sm font-medium text-[#748178]">One last step</p>
            <h1 class="text-3xl font-semibold tracking-[-0.035em]">Verify your email</h1>
            <p class="text-sm leading-6 text-[#58645c]">
                We sent a verification link to your email address. Open that message and follow the link to verify your account.
            </p>
        </div>

        @if (session('status'))
            <div role="status" class="mt-6 rounded-lg border border-[#cbdccf] bg-[#edf5ee] p-4 text-sm text-[#244b3b]">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}" class="mt-8">
            @csrf
            <button type="submit" class="flex h-12 w-full items-center justify-center rounded-lg bg-[#244b3b] px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#193d30] focus:outline-none focus:ring-4 focus:ring-[#527863]/20">
                Resend verification email
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-[#748178]">
            <a href="{{ route('login') }}" class="font-semibold text-[#527863] transition hover:text-[#294b3b]">Back to sign in</a>
        </p>
    </main>
</body>
</html>
