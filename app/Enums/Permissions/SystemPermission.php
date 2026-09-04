<?php

declare(strict_types=1);

namespace App\Enums\Permissions;

use App\Contracts\Permissions\PermissionEnum;

enum SystemPermission: string implements PermissionEnum
{
    case ManageRoles       = 'system.manage_roles';
    case ManagePermissions = 'system.manage_permissions';

    public function label(): string
    {
        return match ($this) {
            self::ManageRoles       => 'ASSIGN OR REVOKE USER ROLES',
            self::ManagePermissions => 'GRANT OR REVOKE PERMISSIONS (ROLE OR USER)',
        };
    }

    public function group(): string
    {
        return 'system';
    }
}
