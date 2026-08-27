<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\UpdateRolePermissionsRequest;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\JsonResponse;

/**
 * مسار المنح الأول: منح/سحب صلاحية لدور كامل (تنطبق على كل مستخدم يحمل هذا الدور).
 */
class RolePermissionController extends Controller
{
    public function index(Role $role): JsonResponse
    {
        return response()->json([
            'data' => $role->load('permissions')->permissions,
        ]);
    }

    public function store(UpdateRolePermissionsRequest $request, Role $role): JsonResponse
    {
        $role->givePermissionTo(...$request->validated('permissions'));

        return response()->json(['data' => $role->fresh('permissions')->permissions]);
    }

    public function destroy(Role $role, Permission $permission): JsonResponse
    {
        $role->revokePermissionTo($permission);

        return response()->json(['data' => $role->fresh('permissions')->permissions]);
    }
}
