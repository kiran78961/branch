@extends('layouts.attendance')

@section('title', 'Leave requests')

@section('content')
    <div>
        <p class="text-sm font-medium text-[#748178]">Time away from work</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-[-0.035em] text-[#18221e] sm:text-4xl">Leave requests</h1>
        <p class="mt-2 text-sm leading-6 text-[#748178]">Submit a request and check its approval status here.</p>
    </div>

    @if ($errors->any())
        <div role="alert" class="mt-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
            Please correct the highlighted leave request details.
        </div>
    @endif

    <section class="mt-8 rounded-2xl border border-[#e4e9e3] bg-white p-5 shadow-sm sm:p-7" aria-labelledby="apply-leave-title">
        <h2 id="apply-leave-title" class="text-xl font-semibold tracking-tight text-[#18221e]">Apply for leave</h2>
        <form method="POST" action="{{ route('leave-requests.store') }}" class="mt-5 grid gap-5 sm:grid-cols-2">
            @csrf
            <div class="space-y-2">
                <label for="leave_type" class="block text-sm font-medium text-[#58645c]">Leave type</label>
                <select id="leave_type" name="leave_type" required class="h-11 w-full rounded-lg border border-[#dce2dc] bg-white px-3 text-sm text-[#18221e] outline-none focus:border-[#527863] focus:ring-4 focus:ring-[#527863]/10">
                    <option value="">Select a type</option>
                    @foreach ($leaveTypes as $value => $label)
                        <option value="{{ $value }}" @selected(old('leave_type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('leave_type')
                    <p class="text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>
            <div class="hidden sm:block"></div>
            <div class="space-y-2">
                <label for="start_date" class="block text-sm font-medium text-[#58645c]">First day</label>
                <input id="start_date" name="start_date" type="date" min="{{ today()->toDateString() }}" value="{{ old('start_date') }}" required class="h-11 w-full rounded-lg border border-[#dce2dc] bg-white px-3 text-sm text-[#18221e] outline-none focus:border-[#527863] focus:ring-4 focus:ring-[#527863]/10">
                @error('start_date')
                    <p class="text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>
            <div class="space-y-2">
                <label for="end_date" class="block text-sm font-medium text-[#58645c]">Last day</label>
                <input id="end_date" name="end_date" type="date" min="{{ today()->toDateString() }}" value="{{ old('end_date') }}" required class="h-11 w-full rounded-lg border border-[#dce2dc] bg-white px-3 text-sm text-[#18221e] outline-none focus:border-[#527863] focus:ring-4 focus:ring-[#527863]/10">
                @error('end_date')
                    <p class="text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>
            <div class="space-y-2 sm:col-span-2">
                <label for="reason" class="block text-sm font-medium text-[#58645c]">Reason</label>
                <textarea id="reason" name="reason" rows="3" maxlength="2000" required class="w-full rounded-lg border border-[#dce2dc] bg-white px-3 py-2.5 text-sm text-[#18221e] outline-none focus:border-[#527863] focus:ring-4 focus:ring-[#527863]/10">{{ old('reason') }}</textarea>
                @error('reason')
                    <p class="text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>
            <div class="sm:col-span-2">
                <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-[#244b3b] px-4 text-sm font-semibold text-white transition hover:bg-[#193d30] focus:outline-none focus:ring-4 focus:ring-[#527863]/20">Submit request</button>
            </div>
        </form>
    </section>

    <section class="mt-9" aria-labelledby="my-leave-requests-title">
        <div>
            <h2 id="my-leave-requests-title" class="text-xl font-semibold tracking-tight text-[#18221e]">My requests</h2>
            <p class="mt-1 text-sm text-[#748178]">Your submitted requests and their latest status.</p>
        </div>

        <div class="mt-5 overflow-hidden rounded-2xl border border-[#e4e9e3] bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-left text-sm">
                    <thead class="bg-[#f7f8f5] text-xs font-semibold uppercase tracking-wide text-[#748178]">
                        <tr>
                            <th scope="col" class="px-5 py-4">Type</th>
                            <th scope="col" class="px-5 py-4">Dates</th>
                            <th scope="col" class="px-5 py-4">Reason</th>
                            <th scope="col" class="px-5 py-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#edf0ec]">
                        @forelse ($leaveRequests as $leaveRequest)
                            <tr>
                                <td class="whitespace-nowrap px-5 py-4 font-medium text-[#303c35]">{{ $leaveTypes[$leaveRequest->leave_type] ?? $leaveRequest->leave_type }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-[#58645c]">{{ $leaveRequest->start_date->format('M j, Y') }} – {{ $leaveRequest->end_date->format('M j, Y') }}</td>
                                <td class="max-w-sm px-5 py-4 text-[#58645c]">{{ $leaveRequest->reason }}</td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ match ($leaveRequest->status) {
                                        'approved' => 'bg-[#edf5ee] text-[#315d48]',
                                        'rejected' => 'bg-red-50 text-red-800',
                                        default => 'bg-[#fbf2e8] text-[#8b5a2a]',
                                    } }}">
                                        {{ ucfirst($leaveRequest->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center text-sm text-[#748178]">You have not submitted any leave requests.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-4">{{ $leaveRequests->links() }}</div>
    </section>
@endsection
