<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class HolidayController extends Controller
{
    public function index(): View
    {
        return view('holidays.index', [
            'holidayYear' => 2026,
            'holidays' => config('holidays.india.2026'),
        ]);
    }
}
