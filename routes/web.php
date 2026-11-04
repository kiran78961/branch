<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('login');
});

Route::view('/login', 'login')->name('login');

Route::view('/register', 'register')->name('register');
