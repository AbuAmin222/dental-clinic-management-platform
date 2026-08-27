<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Renders the permissions catalog page. Create/read of the catalog itself stays on the
 * existing api/admin/permissions endpoints (Api\Admin\PermissionController) — this
 * controller only ever supplies the initial page load so the table isn't empty before any
 * client-side fetch happens.
 */
class PermissionPageController extends Controller
{
    public function index(): InertiaResponse
    {
        return Inertia::render('Admin/Permissions/Index', [
            'permissions' => Permission::orderBy('group')->orderBy('name')->get(),
            'groups' => Permission::orderBy('group')->pluck('group')->unique()->values(),
        ]);
    }
}
