<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Renders the Inertia pages that consume the existing api/admin/roles/{role}/permissions
 * JSON endpoints (RolePermissionController). This controller only ever performs reads —
 * every grant/revoke write still goes exclusively through the API controllers, so there is
 * a single source of truth for the mutation logic (no duplicated authorization/business
 * rules between a "web" and an "api" controller).
 */
class RolePageController extends Controller
{
    public function index(): InertiaResponse
    {
        return Inertia::render('Admin/Roles/Index', [
            'roles' => Role::withCount('users')->orderBy('name')->get(),
        ]);
    }

    public function permissions(Role $role): InertiaResponse
    {
        return Inertia::render('Admin/Roles/Permissions', [
            'role' => $role,
            'allPermissions' => Permission::orderBy('group')->orderBy('name')->get(),
            'grantedPermissionNames' => $role->permissions()->pluck('permissions.name'),
        ]);
    }
}
