<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Sign in · Branch</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f7f8f5] text-[#18221e] antialiased">
    <main class="grid min-h-screen lg:grid-cols-[1.05fr_0.95fr]">
        <section class="relative isolate flex min-h-[420px] flex-col overflow-hidden bg-[#173b32] px-8 py-8 text-white sm:px-12 lg:min-h-screen lg:px-16 lg:py-12" aria-label="About Branch">
            <div class="absolute inset-0 -z-10 overflow-hidden" aria-hidden="true">
                <div class="absolute -left-36 top-24 size-[28rem] rounded-full bg-[#9abf8e]/15 blur-3xl"></div>
                <div class="absolute -bottom-48 right-[-4rem] size-[34rem] rounded-full bg-[#d49d68]/15 blur-3xl"></div>
                <svg class="absolute -bottom-8 right-[-2rem] h-[28rem] w-[28rem] opacity-30 sm:right-0" viewBox="0 0 460 460" fill="none">
                    <path d="M92 427C173 329 222 224 279 28M171 315c-57-6-88-39-95-91 51 3 84 34 95 91ZM213 235c-4-55 22-95 73-119 9 50-16 91-73 119ZM270 117c-46-27-64-70-53-121 46 22 66 64 53 121ZM123 376c-48-20-70-60-64-112 47 16 72 56 64 112ZM231 182c-49-20-71-60-65-111 48 16 72 55 65 111Z" stroke="white" stroke-width="2"/>
                    <circle cx="279" cy="28" r="5" fill="#d49d68"/>
                </svg>
                <svg class="branch-art-float absolute bottom-24 right-10 hidden h-36 w-28 sm:right-16 lg:block" viewBox="0 0 112 144" fill="none" aria-hidden="true">
                    <circle cx="57" cy="61" r="40" fill="#C5D6C6" fill-opacity=".08"/>
                    <path d="M56 96V47" stroke="#D8C8A6" stroke-linecap="round" stroke-width="2.5"/>
                    <path class="branch-art-leaf" d="M56 68C34 66 25 54 28 39c17 2 27 12 28 29Z" fill="#B9C9A8" fill-opacity=".9"/>
                    <path class="branch-art-leaf branch-art-leaf--upper" d="M56 56c2-20 13-29 28-28-1 16-11 26-28 28Z" fill="#D5DFCA"/>
                    <path d="M56 82c-17-1-26-10-25-24 15 1 24 9 25 24Z" fill="#89A88A"/>
                    <path d="M39 97h34l-4 30H43l-4-30Z" fill="#D49D68"/>
                    <rect x="37" y="96" width="38" height="7" rx="3.5" fill="#E2B98C"/>
                    <path d="M48 109h16" stroke="#F0D8B9" stroke-linecap="round" stroke-width="2"/>
                </svg>
            </div>

            <a href="/" class="relative flex w-fit items-center gap-3 text-white no-underline" aria-label="Branch home">
                <span class="grid size-10 place-items-center rounded-xl bg-white/10 ring-1 ring-white/15">
                    <svg class="size-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M6 19V9m0 5 5-5m-5 5-3-3m9 9V5m0 8 6-6m-6 6 5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="12" cy="5" r="1.5" fill="#d49d68"/>
                    </svg>
                </span>
                <span class="text-xl font-semibold tracking-tight">branch</span>
            </a>

            <div class="relative my-auto max-w-xl py-16 lg:py-24">
                <p class="mb-6 flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.22em] text-[#c5d6c6]">
                    <span class="h-px w-8 bg-[#d49d68]"></span>
                    A calmer way to work
                </p>
                <h1 class="max-w-lg text-4xl font-medium leading-[1.12] tracking-[-0.04em] sm:text-5xl lg:text-6xl">
                    Make room for your best work.
                </h1>
                <p class="mt-6 max-w-md text-base leading-7 text-white/70 sm:text-lg sm:leading-8">
                    Bring your people and projects together, and let the good work grow from there.
                </p>
            </div>

            <p class="relative text-xs text-white/45">Thoughtful work starts here.</p>
        </section>

        <section class="flex items-center justify-center px-6 py-14 sm:px-10 lg:px-14" aria-labelledby="login-title">
            <div class="w-full max-w-[420px]">
                <div class="mb-10 lg:hidden">
                    <a href="/" class="inline-flex items-center gap-3 text-[#173b32] no-underline" aria-label="Branch home">
                        <span class="grid size-10 place-items-center rounded-xl bg-[#173b32] text-white">
                            <svg class="size-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M6 19V9m0 5 5-5m-5 5-3-3m9 9V5m0 8 6-6m-6 6 5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                <circle cx="12" cy="5" r="1.5" fill="#d49d68"/>
                            </svg>
                        </span>
                        <span class="text-xl font-semibold tracking-tight">branch</span>
                    </a>
                </div>

                <div class="mb-9">
                    <p class="mb-3 text-sm font-medium text-[#748178]">Welcome back</p>
                    <h2 id="login-title" class="text-3xl font-semibold tracking-[-0.035em] text-[#18221e] sm:text-[2.1rem]">Sign in to Branch</h2>
                    <p class="mt-3 text-sm leading-6 text-[#748178]">Enter your details to pick up where you left off.</p>
                </div>

                <div class="space-y-5" role="group" aria-label="Sign in">
                    <div class="space-y-2">
                        <label for="email" class="block text-sm font-medium text-[#303c35]">Work email</label>
                        <input id="email" name="email" type="email" autocomplete="email" placeholder="you@company.com" class="h-12 w-full rounded-lg border border-[#dce2dc] bg-white px-4 text-sm text-[#18221e] outline-none transition placeholder:text-[#a0aaa2] hover:border-[#aab8ac] focus:border-[#527863] focus:ring-4 focus:ring-[#527863]/10">
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between gap-4">
                            <label for="password" class="block text-sm font-medium text-[#303c35]">Password</label>
                            <a href="#" class="text-sm font-medium text-[#527863] transition hover:text-[#294b3b]">Forgot password?</a>
                        </div>
                        <input id="password" name="password" type="password" autocomplete="current-password" placeholder="Enter your password" class="h-12 w-full rounded-lg border border-[#dce2dc] bg-white px-4 text-sm text-[#18221e] outline-none transition placeholder:text-[#a0aaa2] hover:border-[#aab8ac] focus:border-[#527863] focus:ring-4 focus:ring-[#527863]/10">
                    </div>

                    <label class="flex w-fit cursor-pointer items-center gap-3 py-1 text-sm text-[#58645c]">
                        <input type="checkbox" name="remember" class="size-4 rounded border-[#cbd4cc] accent-[#315d48] focus:ring-[#527863]/20">
                        Keep me signed in
                    </label>

                    <button type="button" class="mt-2 flex h-12 w-full items-center justify-center gap-2 rounded-lg bg-[#244b3b] px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#193d30] focus:outline-none focus:ring-4 focus:ring-[#527863]/20">
                        Sign in
                        <svg class="size-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M4 10h12m-5-5 5 5-5 5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>

                <p class="mt-8 text-center text-sm text-[#748178]">
                    New to Branch? <a href="#" class="font-semibold text-[#527863] transition hover:text-[#294b3b]">Talk to your team admin</a>
                </p>

                <p class="mt-12 text-center text-xs leading-5 text-[#98a199]">
                    By continuing, you agree to Branch's
                    <a href="#" class="underline decoration-[#c4ccc5] underline-offset-2 transition hover:text-[#58645c]">Terms of Service</a>
                    and
                    <a href="#" class="underline decoration-[#c4ccc5] underline-offset-2 transition hover:text-[#58645c]">Privacy Policy</a>.
                </p>
            </div>
        </section>
    </main>
</body>
</html>
