@extends('layouts.attendance')

@section('title', 'Staff management')

@section('content')
    <div>
        <p class="text-sm font-medium text-[#748178]">Administrator workspace</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-[-0.035em] text-[#18221e] sm:text-4xl">Staff management</h1>
        <p class="mt-2 text-sm leading-6 text-[#748178]">Review employee profiles and manage administrator access.</p>
    </div>

    <div class="mt-6">
        <a href="{{ route('admin.staff.create') }}" class="inline-flex h-11 items-center justify-center rounded-lg bg-[#244b3b] px-4 text-sm font-semibold text-white transition hover:bg-[#193d30] focus:outline-none focus:ring-4 focus:ring-[#527863]/20">Add employee</a>
    </div>

    @if ($errors->has('is_admin'))
        <div role="alert" class="mt-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
            {{ $errors->first('is_admin') }}
        </div>
    @endif

    <div class="mt-8 overflow-hidden rounded-2xl border border-[#e4e9e3] bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[680px] text-left text-sm">
                <thead class="bg-[#f7f8f5] text-xs font-semibold uppercase tracking-wide text-[#748178]">
                    <tr>
                        <th scope="col" class="px-5 py-4">Staff member</th>
                        <th scope="col" class="px-5 py-4">Role</th>
                        <th scope="col" class="px-5 py-4">Access</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#edf0ec]">
                    @forelse ($staff as $staffMember)
                        <tr>
                            <td class="px-5 py-4">
                                <a href="{{ route('admin.staff.show', $staffMember) }}" class="font-semibold text-[#315d48] transition hover:text-[#173b32] hover:underline">{{ $staffMember->name }}</a>
                                <p class="mt-1 text-xs text-[#748178]">{{ $staffMember->email }}</p>
                            </td>
                            <td class="px-5 py-4 text-[#58645c]">{{ $staffMember->is_admin ? 'Administrator' : 'Employee' }}</td>
                            <td class="px-5 py-4">
                                @if (! $staffMember->is(auth()->user()))
                                    <form method="POST" action="{{ route('admin.staff.role', $staffMember) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="is_admin" value="{{ $staffMember->is_admin ? '0' : '1' }}">
                                        <button type="submit" class="rounded-lg border border-[#dce2dc] px-3 py-2 text-xs font-semibold text-[#315d48] transition hover:border-[#aab8ac] hover:bg-[#f7f8f5]">
                                            {{ $staffMember->is_admin ? 'Remove admin access' : 'Make administrator' }}
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-[#98a199]">Current account</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-5 py-10 text-center text-sm text-[#748178]">No staff accounts are registered yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $staff->links() }}</div>
@endsection
