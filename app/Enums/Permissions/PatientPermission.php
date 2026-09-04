<?php

declare(strict_types=1);

namespace App\Enums\Permissions;

use App\Contracts\Permissions\PermissionEnum;

enum PatientPermission: string implements PermissionEnum
{
    case ViewAny = 'patients.viewAny';
    case View    = 'patients.view';
    case Create  = 'patients.create';
    case Update  = 'patients.update';
    case Delete  = 'patients.delete';

    public function label(): string
    {
        return match ($this) {
            self::ViewAny => 'VIEW ALL PATIENTS',
            self::View    => 'VIEW PATIENT PROFILE',
            self::Create  => 'REGISTER NEW PATIENT',
            self::Update  => 'UPDATE PATIENT DETAILS',
            self::Delete  => 'DELETE PATIENT',
        };
    }

    public function group(): string
    {
        return 'patients';
    }
}
