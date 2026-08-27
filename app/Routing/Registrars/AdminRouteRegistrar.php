<?php

declare(strict_types=1);

namespace App\Routing\Registrars;

use App\Routing\BaseRoleRouteRegistrar;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PermissionPageController;
use App\Http\Controllers\Admin\RolePageController;
use App\Http\Controllers\Admin\UserActivationController;
use App\Http\Controllers\Admin\UserRbacPageController;
use App\Http\Controllers\Admin\UserSalaryController;
use Illuminate\Routing\Router;

/**
 * Admin-domain Registrar. The `roles.*`, `permissions.*`, `users.index` and
 * `users.rolesPermissions` routes below only render Inertia pages that then talk directly
 * to the existing `routes/api.php` (`auth:sanctum` + `role:admin`) endpoints for every
 * grant/revoke/create mutation — no authorization or business logic is duplicated here.
 * `users.pendingReviews`/`activate`/`reject` are new: closes the previously-flagged gap
 * where `EnsureUserIsActive` could send a user to the pending screen but no admin-side
 * screen or endpoint existed to actually activate them.
 */
class AdminRouteRegistrar extends BaseRoleRouteRegistrar
{
    protected function dashboardAction(): array
    {
        return [DashboardController::class, 'index'];
    }
    public function additionalMiddleware(): array
    {
        return ['onboarding.completed'];
    }

    protected function registerSpecificRoutes(Router $router): void
    {
        $router->prefix('staff-salaries')->name('staffSalaries.')->group(static function (Router $group): void {
            $group->get('/', [UserSalaryController::class, 'index'])->name('index');
            $group->patch('/{user}', [UserSalaryController::class, 'update'])->name('update');
        });

        $router->prefix('roles')->name('roles.')->group(static function (Router $group): void {
            $group->get('/', [RolePageController::class, 'index'])->name('index');
            $group->get('/{role}/permissions', [RolePageController::class, 'permissions'])->name('permissions');
        });

        $router->prefix('permissions')->name('permissions.')->group(static function (Router $group): void {
            $group->get('/', [PermissionPageController::class, 'index'])->name('index');
        });

        $router->prefix('users')->name('users.')->group(static function (Router $group): void {
            $group->get('/pending-reviews', [UserActivationController::class, 'index'])->name('pendingReviews');
            $group->patch('/{user}/activate', [UserActivationController::class, 'activate'])->name('activate');
            $group->patch('/{user}/reject', [UserActivationController::class, 'reject'])->name('reject');

            $group->get('/', [UserRbacPageController::class, 'index'])->name('index');
            $group->get('/{user}/roles-permissions', [UserRbacPageController::class, 'rolesPermissions'])->name('rolesPermissions');
        });
    }
}
