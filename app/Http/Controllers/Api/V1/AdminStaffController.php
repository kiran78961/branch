<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AttendanceResource;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class AdminStaffController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return UserResource::collection(
            User::query()->orderBy('name')->paginate(20),
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        return UserResource::make($user)->response()->setStatusCode(201);
    }

    public function show(User $user): JsonResponse
    {
        $attendances = $user->attendances()
            ->orderByDesc('attendance_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return response()->json([
            'data' => [
                'staff' => UserResource::make($user),
                'attendances' => AttendanceResource::collection($attendances),
            ],
        ]);
    }

    public function updateRole(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'is_admin' => ['required', 'boolean'],
        ]);
        $isAdmin = (bool) $validated['is_admin'];

        if ($user->is($request->user()) && ! $isAdmin) {
            return response()->json([
                'message' => 'You cannot remove your own administrator access.',
            ], 422);
        }

        $user->is_admin = $isAdmin;
        $user->save();

        return UserResource::make($user->refresh())->response();
    }
}
