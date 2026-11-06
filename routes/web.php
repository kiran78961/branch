<?php

use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminLeaveRequestController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\SettingsController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

Route::get('/', function (Request $request): RedirectResponse|View {
    if ($request->user() !== null) {
        return redirect()->route('attendance.index');
    }

    return view('login');
})->name('home');

Route::get('/login', function (Request $request): RedirectResponse|View {
    if ($request->user() !== null) {
        return redirect()->route('attendance.index');
    }

    return view('login');
})->name('login');

Route::post('/login', [LoginController::class, 'store'])
    ->middleware('throttle:login')
    ->name('login.store');

Route::middleware('guest')->group(function () {
    Route::view('/register', 'register')->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/profile', [SettingsController::class, 'profile'])
        ->name('profile.show');
    Route::patch('/profile', [SettingsController::class, 'updateProfile'])
        ->name('profile.update');

    Route::get('/settings', [SettingsController::class, 'edit'])
        ->name('settings.edit');
    Route::patch('/settings/attendance', [SettingsController::class, 'updateAttendancePreferences'])
        ->name('settings.attendance.update');
    Route::patch('/settings/company-attendance', [SettingsController::class, 'updateCompanyAttendancePreferences'])
        ->middleware('can:manage-attendance')
        ->name('settings.company-attendance.update');

    Route::get('/attendance', [AttendanceController::class, 'index'])
        ->name('attendance.index');
    Route::get('/attendance/history', [AttendanceController::class, 'history'])
        ->name('attendance.history');
    Route::get('/holidays', [HolidayController::class, 'index'])
        ->name('holidays.index');
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])
        ->name('attendance.check-in');
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])
        ->name('attendance.check-out');
    Route::post('/attendance/lunch/start', [AttendanceController::class, 'startLunch'])
        ->name('attendance.lunch.start');
    Route::post('/attendance/lunch/end', [AttendanceController::class, 'endLunch'])
        ->name('attendance.lunch.end');
    Route::middleware('can:apply-leave')->group(function () {
        Route::get('/leave-requests', [LeaveRequestController::class, 'index'])
            ->name('leave-requests.index');
        Route::post('/leave-requests', [LeaveRequestController::class, 'store'])
            ->name('leave-requests.store');
    });

    Route::prefix('admin')
        ->name('admin.')
        ->middleware('can:manage-attendance')
        ->group(function () {
            Route::get('/attendance', [AdminAttendanceController::class, 'index'])
                ->name('attendance.index');
            Route::get('/leave-requests', [AdminLeaveRequestController::class, 'index'])
                ->name('leave-requests.index');
            Route::patch('/leave-requests/{leaveRequest}', [AdminLeaveRequestController::class, 'update'])
                ->name('leave-requests.update');
            Route::get('/staff/create', [AdminAttendanceController::class, 'createStaff'])
                ->name('staff.create');
            Route::post('/staff', [AdminAttendanceController::class, 'storeStaff'])
                ->name('staff.store');
            Route::get('/staff/{user}', [AdminAttendanceController::class, 'showStaff'])
                ->name('staff.show');
            Route::get('/staff', [AdminAttendanceController::class, 'staff'])
                ->name('staff.index');
            Route::patch('/staff/{user}/role', [AdminAttendanceController::class, 'updateRole'])
                ->name('staff.role');
        });
});
