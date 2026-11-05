@extends('layouts.attendance')

@section('title', 'Add employee')

@section('content')
    <div>
        <a href="{{ route('admin.staff.index') }}" class="text-sm font-semibold text-[#527863] transition hover:text-[#294b3b]">← Back to staff</a>
        <p class="mt-6 text-sm font-medium text-[#748178]">Administrator workspace</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-[-0.035em] text-[#18221e] sm:text-4xl">Add an employee</h1>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-[#748178]">Create an employee account and set a temporary password. Share the password with the employee securely.</p>
    </div>

    <form method="POST" action="{{ route('admin.staff.store') }}" class="mt-8 max-w-2xl space-y-5 rounded-2xl border border-[#e4e9e3] bg-white p-6 shadow-sm sm:p-8">
        @csrf

        @if ($errors->any())
            <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="space-y-2">
            <label for="name" class="block text-sm font-medium text-[#303c35]">Employee name</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" maxlength="255" required autofocus class="h-12 w-full rounded-lg border border-[#dce2dc] bg-white px-4 text-sm text-[#18221e] outline-none transition placeholder:text-[#a0aaa2] hover:border-[#aab8ac] focus:border-[#527863] focus:ring-4 focus:ring-[#527863]/10">
        </div>

        <div class="space-y-2">
            <label for="email" class="block text-sm font-medium text-[#303c35]">Work email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" maxlength="255" required class="h-12 w-full rounded-lg border border-[#dce2dc] bg-white px-4 text-sm text-[#18221e] outline-none transition placeholder:text-[#a0aaa2] hover:border-[#aab8ac] focus:border-[#527863] focus:ring-4 focus:ring-[#527863]/10">
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div class="space-y-2">
                <label for="password" class="block text-sm font-medium text-[#303c35]">Temporary password</label>
                <input id="password" name="password" type="password" autocomplete="new-password" minlength="8" required class="h-12 w-full rounded-lg border border-[#dce2dc] bg-white px-4 text-sm text-[#18221e] outline-none transition placeholder:text-[#a0aaa2] hover:border-[#aab8ac] focus:border-[#527863] focus:ring-4 focus:ring-[#527863]/10">
                <p class="text-xs leading-5 text-[#748178]">Use at least 8 characters. Share it securely.</p>
            </div>
            <div class="space-y-2">
                <label for="password_confirmation" class="block text-sm font-medium text-[#303c35]">Confirm temporary password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required class="h-12 w-full rounded-lg border border-[#dce2dc] bg-white px-4 text-sm text-[#18221e] outline-none transition placeholder:text-[#a0aaa2] hover:border-[#aab8ac] focus:border-[#527863] focus:ring-4 focus:ring-[#527863]/10">
            </div>
        </div>

        <div class="flex flex-col-reverse gap-3 border-t border-[#edf0ec] pt-5 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.staff.index') }}" class="flex h-11 items-center justify-center rounded-lg border border-[#dce2dc] px-4 text-sm font-semibold text-[#58645c] transition hover:bg-[#f7f8f5]">Cancel</a>
            <button type="submit" class="flex h-11 items-center justify-center rounded-lg bg-[#244b3b] px-5 text-sm font-semibold text-white transition hover:bg-[#193d30] focus:outline-none focus:ring-4 focus:ring-[#527863]/20">Create employee</button>
        </div>
    </form>
@endsection
