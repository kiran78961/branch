<?php

use App\Http\Controllers\Api\V1\AdminAttendanceController;
use App\Http\Controllers\Api\V1\AdminLeaveRequestController;
use App\Http\Controllers\Api\V1\AdminStaffController;
use App\Http\Controllers\Api\V1\AttendanceController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\HolidayController;
use App\Http\Controllers\Api\V1\LeaveRequestController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\SettingsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->name('api.v1.')
    ->group(function (): void {
        Route::post('/auth/register', [AuthController::class, 'register'])
            ->middleware('throttle:10,1')
            ->name('auth.register');
        Route::post('/auth/login', [AuthController::class, 'login'])
            ->middleware('throttle:login')
            ->name('auth.login');

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::post('/auth/logout', [AuthController::class, 'logout'])
                ->name('auth.logout');
            Route::get('/auth/me', [AuthController::class, 'me'])
                ->name('auth.me');

            Route::get('/profile', [ProfileController::class, 'show'])
                ->name('profile.show');
            Route::patch('/profile', [ProfileController::class, 'update'])
                ->name('profile.update');
            Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])
                ->name('profile.avatar');

            Route::get('/attendance', [AttendanceController::class, 'index'])
                ->name('attendance.index');
            Route::get('/attendance/today', [AttendanceController::class, 'today'])
                ->name('attendance.today');
            Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])
                ->name('attendance.check-in');
            Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])
                ->name('attendance.check-out');
            Route::post('/attendance/lunch/start', [AttendanceController::class, 'startLunch'])
                ->name('attendance.lunch.start');
            Route::post('/attendance/lunch/end', [AttendanceController::class, 'endLunch'])
                ->name('attendance.lunch.end');

            Route::get('/holidays', [HolidayController::class, 'index'])
                ->name('holidays.index');
            Route::get('/settings/attendance', [SettingsController::class, 'showAttendance'])
                ->name('settings.attendance.show');
            Route::patch('/settings/attendance', [SettingsController::class, 'updateAttendance'])
                ->name('settings.attendance.update');

            Route::middleware('can:apply-leave')->group(function (): void {
                Route::get('/leave-requests', [LeaveRequestController::class, 'index'])
                    ->name('leave-requests.index');
                Route::post('/leave-requests', [LeaveRequestController::class, 'store'])
                    ->name('leave-requests.store');
            });

            Route::middleware('can:manage-attendance')->prefix('admin')->name('admin.')->group(function (): void {
                Route::get('/reports/attendance', [AdminAttendanceController::class, 'index'])
                    ->name('reports.attendance.index');
                Route::get('/staff', [AdminStaffController::class, 'index'])
                    ->name('staff.index');
                Route::post('/staff', [AdminStaffController::class, 'store'])
                    ->name('staff.store');
                Route::get('/staff/{user}', [AdminStaffController::class, 'show'])
                    ->name('staff.show');
                Route::patch('/staff/{user}/role', [AdminStaffController::class, 'updateRole'])
                    ->name('staff.role');
                Route::get('/leave-requests', [AdminLeaveRequestController::class, 'index'])
                    ->name('leave-requests.index');
                Route::patch('/leave-requests/{leaveRequest}', [AdminLeaveRequestController::class, 'update'])
                    ->name('leave-requests.update');
                Route::get('/settings/attendance', [SettingsController::class, 'showCompanyAttendance'])
                    ->name('settings.attendance.show');
                Route::patch('/settings/attendance', [SettingsController::class, 'updateCompanyAttendance'])
                    ->name('settings.attendance.update');
            });
        });
    });

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
