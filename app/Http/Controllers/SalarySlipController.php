<?php

namespace App\Http\Controllers;

use App\Notifications\SalaryChangedNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class SalarySlipController extends Controller
{
    public function show(Request $request): View
    {
        return view('salary-slip.show', [
            'user' => $request->user(),
            'salaryMonth' => now()->startOfMonth(),
            'salaryHistories' => $request->user()->salaryHistories()
                ->with('changedBy:id,name')
                ->paginate(15, ['*'], 'salary_history_page')
                ->withQueryString(),
            'salaryNotifications' => $request->user()->unreadNotifications()
                ->where('type', SalaryChangedNotification::class)
                ->latest()
                ->paginate(10, ['*'], 'salary_notifications_page')
                ->withQueryString(),
        ]);
    }

    public function markAsRead(Request $request, string $notification): RedirectResponse
    {
        $salaryNotification = $request->user()->notifications()
            ->where('type', SalaryChangedNotification::class)
            ->findOrFail($notification);

        $salaryNotification->markAsRead();

        return redirect()->route('salary-slip.show')
            ->with('status', 'Salary notification marked as read.');
    }

    public function download(Request $request): Response
    {
        $user = $request->user();
        abort_if($user->monthly_salary === null, 404);

        $salaryMonth = now()->startOfMonth();

        return Pdf::loadView('salary-slip.pdf', [
            'user' => $user,
            'salaryMonth' => $salaryMonth,
        ])
            ->setPaper('a4')
            ->download('salary-slip-'.$salaryMonth->format('Y-m').'.pdf');
    }
}
