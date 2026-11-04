<?php

use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\RegisterController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('login');
});

Route::view('/login', 'login')->name('login');

Route::view('/register', 'register')->name('register');

Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])
        ->name('verification.notice');

    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
});
