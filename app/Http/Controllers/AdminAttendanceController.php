<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminAttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date'],
        ]);
        $reportDate = $validated['date'] ?? today()->toDateString();
        $staffCount = User::query()->count();
        $checkedInCount = Attendance::query()
            ->whereDate('attendance_date', $reportDate)
            ->whereNotNull('check_in_at')
            ->count();

        return view('admin.attendance.index', [
            'reportDate' => $reportDate,
            'staffCount' => $staffCount,
            'checkedInCount' => $checkedInCount,
            'checkedOutCount' => Attendance::query()
                ->whereDate('attendance_date', $reportDate)
                ->whereNotNull('check_out_at')
                ->count(),
            'notStartedCount' => $staffCount - $checkedInCount,
            'staff' => User::query()
                ->with(['attendances' => function (HasMany $query) use ($reportDate): void {
                    $query->whereDate('attendance_date', $reportDate);
                }])
                ->orderBy('name')
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function staff(): View
    {
        return view('admin.staff.index', [
            'staff' => User::query()
                ->orderBy('name')
                ->paginate(20),
        ]);
    }

    public function showStaff(User $user): View
    {
        return view('admin.staff.show', [
            'staffMember' => $user,
            'attendances' => $user->attendances()
                ->orderByDesc('attendance_date')
                ->orderByDesc('id')
                ->paginate(15),
        ]);
    }

    public function createStaff(): View
    {
        return view('admin.staff.create');
    }

    public function storeStaff(Request $request): RedirectResponse
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

        return redirect()->route('admin.staff.show', $user)
            ->with('status', 'Employee created. Share the temporary password with them securely.');
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'is_admin' => ['required', 'boolean'],
        ]);
        $isAdmin = (bool) $validated['is_admin'];

        if ($user->is($request->user()) && ! $isAdmin) {
            return back()->withErrors([
                'is_admin' => 'You cannot remove your own administrator access.',
            ]);
        }

        $user->is_admin = $isAdmin;
        $user->save();

        return redirect()->route('admin.staff.index')->with('status', 'Staff access has been updated.');
    }
}
