<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Create an account · Branch</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f7f8f5] text-[#18221e] antialiased">
    <main class="grid min-h-screen lg:grid-cols-[1.05fr_0.95fr]">
        <section class="relative isolate flex min-h-[360px] flex-col overflow-hidden bg-[#173b32] px-8 py-8 text-white sm:px-12 lg:min-h-screen lg:px-16 lg:py-12" aria-label="About Branch">
            <div class="absolute inset-0 -z-10 overflow-hidden" aria-hidden="true">
                <div class="absolute -left-36 top-24 size-[28rem] rounded-full bg-[#9abf8e]/15 blur-3xl"></div>
                <div class="absolute -bottom-48 right-[-4rem] size-[34rem] rounded-full bg-[#d49d68]/15 blur-3xl"></div>
                <svg class="absolute -bottom-8 right-[-2rem] h-[28rem] w-[28rem] opacity-30 sm:right-0" viewBox="0 0 460 460" fill="none">
                    <path d="M92 427C173 329 222 224 279 28M171 315c-57-6-88-39-95-91 51 3 84 34 95 91ZM213 235c-4-55 22-95 73-119 9 50-16 91-73 119ZM270 117c-46-27-64-70-53-121 46 22 66 64 53 121ZM123 376c-48-20-70-60-64-112 47 16 72 56 64 112ZM231 182c-49-20-71-60-65-111 48 16 72 55 65 111Z" stroke="white" stroke-width="2"/>
                    <circle cx="279" cy="28" r="5" fill="#d49d68"/>
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

            <div class="relative my-auto max-w-xl py-12 lg:py-24">
                <p class="mb-6 flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.22em] text-[#c5d6c6]">
                    <span class="h-px w-8 bg-[#d49d68]"></span>
                    A calmer way to work
                </p>
                <h1 class="max-w-lg text-4xl font-medium leading-[1.12] tracking-[-0.04em] sm:text-5xl lg:text-6xl">
                    Good work grows better together.
                </h1>
                <p class="mt-6 max-w-md text-base leading-7 text-white/70 sm:text-lg sm:leading-8">
                    Make space for your people, your ideas, and the next thing you’ll grow.
                </p>
            </div>

            <p class="relative text-xs text-white/45">Thoughtful work starts here.</p>
        </section>

        <section class="flex items-center justify-center px-6 py-12 sm:px-10 lg:px-14" aria-labelledby="register-title">
            <div class="w-full max-w-[440px]">
                <div class="mb-9 lg:hidden">
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

                <div class="mb-7">
                    <p class="mb-3 text-sm font-medium text-[#748178]">Your next chapter starts here</p>
                    <h2 id="register-title" class="text-3xl font-semibold tracking-[-0.035em] text-[#18221e] sm:text-[2.1rem]">Create your account</h2>
                    <p class="mt-3 text-sm leading-6 text-[#748178]">A few details and you’ll be ready to grow together. We’ll send you an email to verify your address.</p>
                </div>

                <form method="POST" action="{{ route('register.store') }}" class="space-y-4">
                    @csrf

                    @if ($errors->any())
                        <div role="alert" class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                            <ul class="list-inside list-disc space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <label for="first_name" class="block text-sm font-medium text-[#303c35]">First name</label>
                            <input id="first_name" name="first_name" type="text" value="{{ old('first_name') }}" autocomplete="given-name" placeholder="Alex" maxlength="120" required class="h-12 w-full rounded-lg border border-[#dce2dc] bg-white px-4 text-sm text-[#18221e] outline-none transition placeholder:text-[#a0aaa2] hover:border-[#aab8ac] focus:border-[#527863] focus:ring-4 focus:ring-[#527863]/10">
                        </div>
                        <div class="space-y-2">
                            <label for="last_name" class="block text-sm font-medium text-[#303c35]">Last name</label>
                            <input id="last_name" name="last_name" type="text" value="{{ old('last_name') }}" autocomplete="family-name" placeholder="Morgan" maxlength="120" required class="h-12 w-full rounded-lg border border-[#dce2dc] bg-white px-4 text-sm text-[#18221e] outline-none transition placeholder:text-[#a0aaa2] hover:border-[#aab8ac] focus:border-[#527863] focus:ring-4 focus:ring-[#527863]/10">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="email" class="block text-sm font-medium text-[#303c35]">Work email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" placeholder="you@company.com" maxlength="255" required class="h-12 w-full rounded-lg border border-[#dce2dc] bg-white px-4 text-sm text-[#18221e] outline-none transition placeholder:text-[#a0aaa2] hover:border-[#aab8ac] focus:border-[#527863] focus:ring-4 focus:ring-[#527863]/10">
                    </div>

                    <div class="space-y-2">
                        <label for="password" class="block text-sm font-medium text-[#303c35]">Password</label>
                        <input id="password" name="password" type="password" autocomplete="new-password" placeholder="Create a password" minlength="8" required class="h-12 w-full rounded-lg border border-[#dce2dc] bg-white px-4 text-sm text-[#18221e] outline-none transition placeholder:text-[#a0aaa2] hover:border-[#aab8ac] focus:border-[#527863] focus:ring-4 focus:ring-[#527863]/10">
                    </div>

                    <div class="space-y-2">
                        <label for="password_confirmation" class="block text-sm font-medium text-[#303c35]">Confirm password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" placeholder="Enter your password again" minlength="8" required class="h-12 w-full rounded-lg border border-[#dce2dc] bg-white px-4 text-sm text-[#18221e] outline-none transition placeholder:text-[#a0aaa2] hover:border-[#aab8ac] focus:border-[#527863] focus:ring-4 focus:ring-[#527863]/10">
                    </div>

                    <label class="flex cursor-pointer items-start gap-3 py-1 text-sm leading-5 text-[#58645c]">
                        <input type="checkbox" name="terms" value="1" @checked(old('terms')) required class="mt-0.5 size-4 shrink-0 rounded border-[#cbd4cc] accent-[#315d48] focus:ring-[#527863]/20">
                        <span>I agree to Branch’s <a href="#" class="font-medium text-[#527863] underline decoration-[#c4ccc5] underline-offset-2 transition hover:text-[#294b3b]">Terms of Service</a> and <a href="#" class="font-medium text-[#527863] underline decoration-[#c4ccc5] underline-offset-2 transition hover:text-[#294b3b]">Privacy Policy</a>.</span>
                    </label>

                    <button type="submit" class="mt-1 flex h-12 w-full items-center justify-center gap-2 rounded-lg bg-[#244b3b] px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#193d30] focus:outline-none focus:ring-4 focus:ring-[#527863]/20">
                        Create account
                        <svg class="size-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M4 10h12m-5-5 5 5-5 5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </form>

                <p class="mt-7 text-center text-sm text-[#748178]">
                    Already have an account? <a href="{{ route('login') }}" class="font-semibold text-[#527863] transition hover:text-[#294b3b]">Sign in</a>
                </p>
            </div>
        </section>
    </main>
</body>
</html>
