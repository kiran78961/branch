<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function showAttendance(Request $request): JsonResponse
    {
        $user = $request->user();
        $companySettings = DB::table('company_settings')
            ->where('id', 1)
            ->firstOrFail(['workday_start_time']);

        return response()->json([
            'data' => [
                'lunch_start_time' => substr($user->lunch_start_time ?? '13:00', 0, 5),
                'lunch_end_time' => substr($user->lunch_end_time ?? '14:00', 0, 5),
                'daily_work_hours' => (int) $user->daily_work_hours,
                'company_start_time' => substr($companySettings->workday_start_time, 0, 5),
            ],
        ]);
    }

    public function updateAttendance(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lunch_start_time' => ['required', 'date_format:H:i'],
            'lunch_end_time' => ['required', 'date_format:H:i', 'after:lunch_start_time'],
            'daily_work_hours' => ['required', 'integer', 'between:1,24'],
        ]);
        $user = $request->user();
        $user->lunch_start_time = $validated['lunch_start_time'];
        $user->lunch_end_time = $validated['lunch_end_time'];
        $user->daily_work_hours = $validated['daily_work_hours'];
        $user->save();

        return $this->showAttendance($request);
    }

    public function showCompanyAttendance(): JsonResponse
    {
        $companySettings = DB::table('company_settings')
            ->where('id', 1)
            ->firstOrFail(['workday_start_time']);

        return response()->json([
            'data' => [
                'workday_start_time' => substr($companySettings->workday_start_time, 0, 5),
            ],
        ]);
    }

    public function updateCompanyAttendance(Request $request): JsonResponse
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

        return $this->showCompanyAttendance();
    }
}
