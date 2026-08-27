<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Renders the "which users have which roles/permissions" admin screens. All actual
 * grant/revoke/role-change mutations still happen exclusively through
 * Api\Admin\{UserRoleController,UserPermissionController} — this controller is read-only.
 */
class UserRbacPageController extends Controller
{
    public function index(): InertiaResponse
    {
        $users = User::query()
            ->select(['id', 'first_name', 'last_name', 'email', 'is_active'])
            ->with(['roles:id,name,display_name'])
            ->orderBy('last_name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
        ]);
    }

    public function rolesPermissions(User $user): InertiaResponse
    {
        $direct = $user->permissions()->get()->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'display_name' => $p->display_name, 'group' => $p->group, 'source' => 'direct']);

        $viaRoles = $user->roles->flatMap(
            fn (Role $role) => $role->permissions->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'display_name' => $p->display_name,
                'group' => $p->group,
                'source' => "role:{$role->name}",
            ])
        );

        return Inertia::render('Admin/Users/RolesPermissions', [
            'user' => $user->only(['id', 'first_name', 'last_name', 'email']),
            'userRoles' => $user->roles()->get(['roles.id', 'roles.name', 'roles.display_name'])->map(
                fn (Role $r) => ['id' => $r->id, 'name' => $r->name, 'display_name' => $r->display_name, 'is_primary' => (bool) $r->pivot->is_primary]
            ),
            'allRoles' => Role::orderBy('name')->get(['id', 'name', 'display_name']),
            'allPermissions' => Permission::orderBy('group')->orderBy('name')->get(),
            'effectivePermissions' => $direct->concat($viaRoles)->unique('name')->values(),
        ]);
    }
}
