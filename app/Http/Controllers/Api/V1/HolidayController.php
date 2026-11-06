<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'year' => ['nullable', 'integer', 'between:2000,2100'],
        ]);
        $year = (string) ($validated['year'] ?? now()->year);

        return response()->json([
            'data' => [
                'year' => (int) $year,
                'holidays' => config("holidays.india.{$year}", []),
            ],
        ]);
    }
}
