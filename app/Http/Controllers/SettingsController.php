<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();
        $companySettings = DB::table('company_settings')
            ->where('id', 1)
            ->firstOrFail(['workday_start_time']);

        return view('settings.edit', [
            'user' => $user,
            'companyStartTime' => substr($companySettings->workday_start_time, 0, 5),
            'isAdmin' => (bool) $user->is_admin,
            'lunchStartTime' => substr($user->lunch_start_time ?? '13:00', 0, 5),
            'lunchEndTime' => substr($user->lunch_end_time ?? '14:00', 0, 5),
            'dailyWorkHours' => (int) $user->daily_work_hours,
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user)],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->save();

        return redirect()->route('settings.edit')->with('status', 'Your profile has been updated.');
    }

    public function updateAttendancePreferences(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'lunch_start_time' => ['required', 'date_format:H:i'],
            'lunch_end_time' => ['required', 'date_format:H:i', 'after:lunch_start_time'],
            'daily_work_hours' => ['required', 'integer', 'between:1,24'],
        ]);

        $request->user()->lunch_start_time = $validated['lunch_start_time'];
        $request->user()->lunch_end_time = $validated['lunch_end_time'];
        $request->user()->daily_work_hours = $validated['daily_work_hours'];
        $request->user()->save();

        return redirect()->route('settings.edit')->with('status', 'Your attendance preferences have been updated.');
    }

    public function updateCompanyAttendancePreferences(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'workday_start_time' => ['required', 'date_format:H:i'],
        ]);

        DB::table('company_settings')
            ->where('id', 1)
            ->firstOrFail(['id']);
        DB::table('company_settings')
            ->where('id', 1)
            ->update([
                'workday_start_time' => $validated['workday_start_time'],
                'updated_at' => now(),
            ]);

        return redirect()->route('settings.edit')->with('status', 'The company start time has been updated.');
    }
}
