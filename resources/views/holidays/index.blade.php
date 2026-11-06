@extends('layouts.attendance')

@section('title', 'Holidays')

@section('content')
    <div>
        <p class="text-sm font-medium text-[#748178]">India-wide holidays</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-[-0.035em] text-[#18221e] sm:text-4xl">
            {{ $holidayYear }} holiday list
        </h1>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-[#748178]">
            India-wide dates from India Post. State and local holiday schedules may differ.
        </p>
    </div>

    <div class="mt-8 overflow-hidden rounded-2xl border border-[#e4e9e3] bg-white shadow-sm">
        <ul class="divide-y divide-[#edf0ec]" aria-label="India-wide holidays for {{ $holidayYear }}">
            @foreach ($holidays as $holiday)
                <li class="flex items-center gap-4 px-5 py-4 sm:px-6">
                    <time datetime="{{ $holiday['date'] }}" class="w-20 shrink-0 text-sm font-semibold tabular-nums text-[#315d48]">
                        {{ \Illuminate\Support\Carbon::parse($holiday['date'])->format('d M') }}
                    </time>
                    <span class="text-sm font-medium leading-5 text-[#303c35]">{{ $holiday['name'] }}</span>
                </li>
            @endforeach
        </ul>
    </div>

    <p class="mt-5 text-sm text-[#748178]">
        Source:
        <a href="https://www.indiapost.gov.in/holidays-list" target="_blank" rel="noopener noreferrer" class="font-medium text-[#315d48] underline decoration-[#a8b9aa] underline-offset-4">
            India Post
        </a>
    </p>
@endsection
