<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Enums\AdminAccessLevel;
use App\Enums\UserRole;
use App\Exceptions\BusinessRuleViolationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\AssignUserRoleRequest;
use App\Models\Admin;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserRoleController extends Controller
{
    public function index(User $user): JsonResponse
    {
        return response()->json(['data' => $user->roles]);
    }

    public function store(AssignUserRoleRequest $request, User $user): JsonResponse
    {
        $user->assignRole($request->validated('role'), isPrimary: (bool) $request->boolean('is_primary'));

        return response()->json(['data' => $user->fresh('roles')->roles]);
    }

    public function destroy(User $user, Role $role): JsonResponse
    {
        if (
            $role->name === UserRole::Admin->value
            && $user->admin?->access_level === AdminAccessLevel::SuperAdmin
            && Admin::where('access_level', AdminAccessLevel::SuperAdmin)->count() <= 1
        ) {
            throw new BusinessRuleViolationException(
                __('Cannot remove the admin role from the last remaining super admin — this would lock the system out of role/permission management entirely.')
            );
        }

        $user->removeRole($role);

        return response()->json(['data' => $user->fresh('roles')->roles]);
    }
}
