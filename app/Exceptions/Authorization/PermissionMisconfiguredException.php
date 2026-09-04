<?php

declare(strict_types=1);

namespace App\Exceptions\Authorization;

use Throwable;

/**
 * Thrown when a Policy references a permission (via a Permission enum
 * case, e.g. AppointmentPermission::View) that does not exist as a row
 * in the `permissions` table — meaning PermissionSeeder has drifted from
 * the Permission enums in App\Enums\Permissions, or a permission row was
 * renamed/deleted directly from the database/admin panel.
 *
 * This is a configuration/ops bug, NEVER a legitimate "user lacks
 * permission" case. The project's own User::hasPermissionTo() cannot
 * distinguish the two on its own (it simply returns false either way),
 * so PermissionGate checks existence explicitly and reports this
 * exception when the row is missing, then fails closed (denies access)
 * rather than letting the ambiguity pass silently.
 */
final class PermissionMisconfiguredException extends AuthorizationException
{
    public function __construct(
        public readonly string $permissionName,
        public readonly int $userId,
        public readonly string $userRole,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            "Permission [{$permissionName}] referenced by a Policy is missing from the `permissions` table. Run/verify PermissionSeeder against App\\Enums\\Permissions.",
            0,
            $previous
        );
    }

    public function getStatusCode(): int
    {
        return 500;
    }

    public function getDomainCode(): int
    {
        return 1002;
    }

    public function getErrorDetails(): mixed
    {
        return [
            'permission' => $this->permissionName,
            'user_id'    => $this->userId,
            'role'       => $this->userRole,
        ];
    }
}
