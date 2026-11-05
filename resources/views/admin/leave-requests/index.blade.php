@extends('layouts.attendance')

@section('title', 'Leave requests')

@section('content')
    <div>
        <p class="text-sm font-medium text-[#748178]">Administrator workspace</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-[-0.035em] text-[#18221e] sm:text-4xl">Leave requests</h1>
        <p class="mt-2 text-sm leading-6 text-[#748178]">Review employee requests and approve or reject pending leave.</p>
    </div>

    @if ($errors->has('status'))
        <div role="alert" class="mt-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
            {{ $errors->first('status') }}
        </div>
    @endif

    <section class="mt-8" aria-labelledby="all-leave-requests-title">
        <h2 id="all-leave-requests-title" class="sr-only">All leave requests</h2>
        <div class="overflow-hidden rounded-2xl border border-[#e4e9e3] bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-left text-sm">
                    <thead class="bg-[#f7f8f5] text-xs font-semibold uppercase tracking-wide text-[#748178]">
                        <tr>
                            <th scope="col" class="px-5 py-4">Employee</th>
                            <th scope="col" class="px-5 py-4">Type</th>
                            <th scope="col" class="px-5 py-4">Dates</th>
                            <th scope="col" class="px-5 py-4">Reason</th>
                            <th scope="col" class="px-5 py-4">Status</th>
                            <th scope="col" class="px-5 py-4">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#edf0ec]">
                        @forelse ($leaveRequests as $leaveRequest)
                            <tr>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-[#303c35]">{{ $leaveRequest->user->name }}</p>
                                    <p class="mt-1 text-xs text-[#748178]">{{ $leaveRequest->user->email }}</p>
                                </td>
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
                                    @if ($leaveRequest->reviewer)
                                        <p class="mt-1 text-xs text-[#748178]">by {{ $leaveRequest->reviewer->name }}</p>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    @if ($leaveRequest->status === 'pending')
                                        <div class="flex flex-wrap gap-2">
                                            <form method="POST" action="{{ route('admin.leave-requests.update', $leaveRequest) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="approved">
                                                <button type="submit" class="rounded-lg bg-[#244b3b] px-3 py-2 text-xs font-semibold text-white transition hover:bg-[#193d30]">Approve</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.leave-requests.update', $leaveRequest) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="rejected">
                                                <button type="submit" class="rounded-lg border border-[#dce2dc] px-3 py-2 text-xs font-semibold text-[#58645c] transition hover:bg-[#f7f8f5]">Reject</button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-xs text-[#748178]">Reviewed {{ $leaveRequest->reviewed_at?->format('M j, Y') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-sm text-[#748178]">There are no leave requests to review.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-4">{{ $leaveRequests->links() }}</div>
    </section>
@endsection
