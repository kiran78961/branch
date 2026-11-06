@extends('layouts.attendance')

@section('title', 'My Profile')

@section('content')
    <div>
        <p class="text-sm font-medium text-[#748178]">Your account</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-[-0.035em] text-[#18221e] sm:text-4xl">My Profile</h1>
        <p class="mt-3 text-sm leading-6 text-[#748178]">View and update your account details.</p>
    </div>

    <section class="mt-8 max-w-3xl rounded-2xl border border-[#e4e9e3] bg-white p-6 shadow-sm sm:p-8" aria-labelledby="profile-heading">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 id="profile-heading" class="text-xl font-semibold tracking-tight text-[#18221e]">Profile details</h2>
                <p class="mt-2 text-sm text-[#748178]">Your profile is visible only to you on this page.</p>
            </div>
            <span class="rounded-full bg-[#edf5ee] px-3 py-1.5 text-xs font-semibold text-[#315d48]">
                {{ $user->is_admin ? 'Administrator' : 'Employee' }}
            </span>
        </div>

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 grid gap-5 sm:grid-cols-2">
            @csrf
            @method('PATCH')
            <div class="flex items-center gap-4 sm:col-span-2">
                @if ($user->avatar_path)
                    <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($user->avatar_path) }}" alt="{{ $user->name }} profile picture" class="size-20 rounded-full object-cover">
                @else
                    <div aria-label="No profile picture uploaded" class="flex size-20 items-center justify-center rounded-full bg-[#edf5ee] text-2xl font-semibold text-[#315d48]">
                        {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                    </div>
                @endif
                <div>
                    <label for="avatar" class="block text-sm font-medium text-[#303c35]">Profile picture</label>
                    <input id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp" class="mt-2 block w-full text-sm text-[#58645c] file:mr-3 file:rounded-lg file:border-0 file:bg-[#edf5ee] file:px-3 file:py-2 file:text-sm file:font-semibold file:text-[#315d48]">
                    <p class="mt-1 text-xs text-[#748178]">JPG, PNG, or WebP up to 2 MB.</p>
                    @error('avatar')
                        <p role="alert" class="mt-2 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <div>
                <label for="name" class="block text-sm font-medium text-[#303c35]">Name</label>
                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required maxlength="255" autocomplete="name" class="mt-2 block h-11 w-full rounded-lg border border-[#dce2dc] bg-white px-3 text-sm text-[#18221e] focus:border-[#527863] focus:outline-none focus:ring-4 focus:ring-[#527863]/20">
                @error('name')
                    <p role="alert" class="mt-2 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-[#303c35]">Email address</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required maxlength="255" autocomplete="email" class="mt-2 block h-11 w-full rounded-lg border border-[#dce2dc] bg-white px-3 text-sm text-[#18221e] focus:border-[#527863] focus:outline-none focus:ring-4 focus:ring-[#527863]/20">
                @error('email')
                    <p role="alert" class="mt-2 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>
            <div class="sm:col-span-2">
                <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-[#173b32] px-5 text-sm font-semibold text-white transition hover:bg-[#294b3b] focus:outline-none focus:ring-4 focus:ring-[#527863]/20">
                    Save profile
                </button>
            </div>
        </form>
    </section>
@endsection
