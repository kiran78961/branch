<?php

namespace App\Actions;

use App\Models\User;
use App\Notifications\SalaryChangedNotification;
use Illuminate\Support\Facades\DB;

class RecordSalaryChange
{
    public function handle(User $user, string $monthlySalary, ?User $changedBy): void
    {
        DB::transaction(function () use ($user, $monthlySalary, $changedBy): void {
            $user = User::query()
                ->lockForUpdate()
                ->findOrFail($user->getKey());

            if ($user->monthly_salary !== null && (float) $user->monthly_salary === (float) $monthlySalary) {
                return;
            }

            $changeType = $user->monthly_salary === null ? 'assigned' : 'updated';
            $user->monthly_salary = $monthlySalary;
            $user->save();

            $user->salaryHistories()->create([
                'changed_by' => $changedBy?->getKey(),
                'monthly_salary' => $user->monthly_salary,
                'change_type' => $changeType,
                'changed_at' => now(),
            ]);

            $user->notify(new SalaryChangedNotification);
        });
    }
}
