<?php

declare(strict_types=1);

namespace App\Enums\Permissions;

use App\Contracts\Permissions\PermissionEnum;

enum PricingPermission: string implements PermissionEnum
{
    case ViewAny     = 'pricings.viewAny';
    case View        = 'pricings.view';
    case Create      = 'pricings.create';
    case Update      = 'pricings.update';
    case Delete      = 'pricings.delete';
    case Restore     = 'pricings.restore';
    case ForceDelete = 'pricings.forceDelete';

    public function label(): string
    {
        return match ($this) {
            self::ViewAny     => 'VIEW PRICINGS LIST',
            self::View        => 'VIEW SERVICE PRICING',
            self::Create      => 'ADD SERVICE PRICING',
            self::Update      => 'UPDATE SERVICE PRICING',
            self::Delete      => 'DELETE SERVICE PRICING',
            self::Restore     => 'RESTORE DELETED SERVICE PRICING',
            self::ForceDelete => 'PERMANENTLY DELETE SERVICE PRICING',
        };
    }

    public function group(): string
    {
        return 'pricings';
    }
}
