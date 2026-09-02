<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserSalaryRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class UserSalaryController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $staffRoles = UserRole::staffRoleValues();

        $staff = User::whereHas('roles', fn($query) => $query->whereIn('name', $staffRoles))
            ->select(['id', 'first_name', 'last_name', 'base_salary'])
            ->with(['roles' => fn($query) => $query->wherePivot('is_primary', true)])
            ->get()
            ->sortBy(fn(User $user) => [$user->role, $user->last_name])
            ->values();

        return Inertia::render('Admin/Staff/Salaries', ['staff' => $staff]);
    }

    public function update(UpdateUserSalaryRequest $request, User $user): RedirectResponse
    {
        $this->authorize('manageSalary', User::class);

        abort_unless(
            $user->hasRole(UserRole::staffRoleValues()),
            422,
            'A salary can only be set for a staff role (doctor, receptionist, financial, or admin).'
        );

        $user->forceFill(['base_salary' => $request->validated('base_salary')])->save();

        return redirect()->back()->with('success', __('Base salary updated.'));
    }
}
