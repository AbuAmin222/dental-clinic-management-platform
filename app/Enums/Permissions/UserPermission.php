<?php

declare(strict_types=1);

namespace App\Enums\Permissions;

use App\Contracts\Permissions\PermissionEnum;

enum UserPermission: string implements PermissionEnum
{
    case ViewAny      = 'users.viewAny';
    case View         = 'users.view';
    case Create       = 'users.create';
    case Update       = 'users.update';
    case Delete       = 'users.delete';
    case Activate     = 'users.activate';
    case ManageSalary = 'users.manage_salary';

    public function label(): string
    {
        return match ($this) {
            self::ViewAny      => 'VIEW ALL USERS',
            self::View         => 'VIEW USER PROFILE',
            self::Create       => 'CREATE USER ACCOUNT',
            self::Update       => 'UPDATE USER ACCOUNT',
            self::Delete       => 'DELETE OR DISABLE USER ACCOUNT',
            self::Activate     => 'ACTIVATE PENDING USER ACCOUNT',
            self::ManageSalary => 'SET EMPLOYEE BASE SALARY',
        };
    }

    public function group(): string
    {
        return 'users';
    }
}
