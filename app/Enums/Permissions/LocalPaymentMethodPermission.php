<?php

declare(strict_types=1);

namespace App\Enums\Permissions;

use App\Contracts\Permissions\PermissionEnum;

enum LocalPaymentMethodPermission: string implements PermissionEnum
{
    case Manage = 'local_payment_methods.manage';

    public function label(): string
    {
        return match ($this) {
            self::Manage => 'MANAGE LOCAL PAYMENT METHODS DISPLAYED TO PATIENTS',
        };
    }

    public function group(): string
    {
        return 'local_payment_methods';
    }
}
