<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\UpdateUserPermissionsRequest;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * مسار المنح الثاني: منح/سحب صلاحية لمستخدم واحد بعينه، بمعزل تام عن دوره — يحل بالضبط
 * الحالة المطلوبة: "موظف استقبال واحد تحديداً يحصل على صلاحية إضافية دون منحها لكل
 * موظفي الاستقبال". يُسجَّل مانح الصلاحية (`granted_by`) تلقائياً لكل عملية.
 */
class UserPermissionController extends Controller
{
    /**
     * الصلاحيات الفعلية للمستخدم: المباشرة + الموروثة من أدواره، مع توضيح مصدر كل واحدة.
     */
    public function index(User $user): JsonResponse
    {
        $direct = $user->permissions()->get()->map(fn ($p) => ['name' => $p->name, 'source' => 'direct']);
        $viaRoles = $user->roles->flatMap(
            fn ($role) => $role->permissions->map(fn ($p) => ['name' => $p->name, 'source' => "role:{$role->name}"])
        );

        return response()->json([
            'data' => $direct->concat($viaRoles)->unique('name')->values(),
        ]);
    }

    public function store(UpdateUserPermissionsRequest $request, User $user): JsonResponse
    {
        foreach ($request->validated('permissions') as $permissionName) {
            $user->givePermissionTo($permissionName, grantedBy: $request->user());
        }

        return response()->json(['data' => $user->fresh('permissions')->permissions]);
    }

    public function destroy(User $user, Permission $permission): JsonResponse
    {
        $user->revokePermissionTo($permission);

        return response()->json(['data' => $user->fresh('permissions')->permissions]);
    }
}
