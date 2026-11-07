<section class="mt-8" aria-labelledby="salary-history-title">
    <div>
        <h2 id="salary-history-title" class="text-xl font-semibold tracking-tight text-[#18221e]">Salary history</h2>
        <p class="mt-1 text-sm text-[#748178]">A record of salary assignments and changes.</p>
    </div>

    <div class="mt-4 overflow-hidden rounded-2xl border border-[#e4e9e3] bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px] text-left text-sm">
                <thead class="bg-[#f7f8f5] text-xs font-semibold uppercase tracking-wide text-[#748178]">
                    <tr>
                        <th scope="col" class="px-5 py-4">Monthly salary</th>
                        <th scope="col" class="px-5 py-4">Record</th>
                        <th scope="col" class="px-5 py-4">Date</th>
                        <th scope="col" class="px-5 py-4">Changed by</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#edf0ec]">
                    @forelse ($salaryHistories as $salaryHistory)
                        <tr>
                            <td class="whitespace-nowrap px-5 py-4 font-medium text-[#303c35]">₹{{ number_format((float) $salaryHistory->monthly_salary, 2) }}</td>
                            <td class="px-5 py-4 text-[#58645c]">
                                {{ match ($salaryHistory->change_type) {
                                    'snapshot' => 'Existing salary snapshot',
                                    'assigned' => 'Salary assigned',
                                    default => 'Salary changed',
                                } }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-[#58645c]">{{ $salaryHistory->changed_at->format('M j, Y g:i A') }}</td>
                            <td class="px-5 py-4 text-[#58645c]">{{ $salaryHistory->changedBy?->name ?? ($salaryHistory->change_type === 'snapshot' ? 'Before history tracking' : 'Administrator unavailable') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center text-sm text-[#748178]">No salary history has been recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $salaryHistories->links() }}</div>
</section>
