<?php

declare(strict_types=1);

namespace App\Services\Authorization;

use App\Contracts\Permissions\PermissionEnum;
use App\Exceptions\Authorization\PermissionMisconfiguredException;
use App\Models\Permission;
use App\Models\User;

/**
 * Single-responsibility, stateless authorization gate combining a coarse
 * role check with a fine-grained permission check against the project's
 * own hand-rolled Permission system (App\Models\Permission /
 * User::hasPermissionTo()) — no third-party ACL package is installed.
 *
 * User::hasPermissionTo() never throws: it simply returns false whether
 * the user lacks the permission OR the permission row doesn't exist at
 * all. Those are two very different failure modes (the second is a
 * PermissionSeeder / App\Enums\Permissions drift bug, not a normal
 * access denial), so this gate checks existence itself and reports the
 * drift case explicitly instead of silently denying with no signal.
 */
final class PermissionGate
{
    /**
     * Memoized once per request: avoids one extra query per Policy check
     * without risking a stale cross-request cache masking a fix an admin
     * just made in the `permissions` table.
     *
     * @var array<string, true>|null
     */
    private static ?array $existingPermissionNames = null;

    /** @param string[] $allowedRoles */
    public function allows(User $user, array $allowedRoles, PermissionEnum $permission): bool
    {
        if (! in_array($user->role, $allowedRoles, true)) {
            return false;
        }

        if (! $this->permissionExistsInDatabase($permission)) {
            report(new PermissionMisconfiguredException(
                permissionName: $permission->value,
                userId: $user->id,
                userRole: $user->role,
            ));

            return false; // Fail-closed until the misconfiguration is fixed.
        }

        return $user->hasPermissionTo($permission->value);
    }

    private function permissionExistsInDatabase(PermissionEnum $permission): bool
    {
        self::$existingPermissionNames ??= Permission::query()
            ->pluck('name')
            ->flip()
            ->map(fn() => true)
            ->all();

        return isset(self::$existingPermissionNames[$permission->value]);
    }
}
