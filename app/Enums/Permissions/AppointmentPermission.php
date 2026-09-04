<?php

declare(strict_types=1);

namespace App\Enums\Permissions;

use App\Contracts\Permissions\PermissionEnum;

enum AppointmentPermission: string implements PermissionEnum
{
    case ViewAny = 'appointments.viewAny';
    case View    = 'appointments.view';
    case Create  = 'appointments.create';
    case Update  = 'appointments.update';
    case Delete  = 'appointments.delete';

    public function label(): string
    {
        return match ($this) {
            self::ViewAny => 'SHOW ALL APPOINTEMTS',
            self::View    => 'SHOW APPOINTMENT DETAILED',
            self::Create  => 'CREATE NEW APPOINTMENT',
            self::Update  => 'EDIT APPOINTMENT',
            self::Delete  => 'CANCCELED|DELETED APPOINTMENT',
        };
    }

    public function group(): string
    {
        return 'appointments';
    }
}
