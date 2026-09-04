<?php

declare(strict_types=1);

namespace App\Enums\Permissions;

use App\Contracts\Permissions\PermissionEnum;

enum InvoicePermission: string implements PermissionEnum
{
    case ViewAny     = 'invoices.viewAny';
    case View        = 'invoices.view';
    case Create      = 'invoices.create';
    case Update      = 'invoices.update';
    case Delete      = 'invoices.delete';
    case Restore     = 'invoices.restore';
    case ForceDelete = 'invoices.forceDelete';
    case Pay         = 'invoices.pay';
    case Issue       = 'invoices.issue';

    public function label(): string
    {
        return match ($this) {
            self::ViewAny     => 'SHOW ALL INVOICES',
            self::View        => 'SHOW INVOICE',
            self::Create      => 'REQUEST A NEW INVOICE (RECEIPT)',
            self::Update      => 'EDIT INVOICE',
            self::Delete      => 'DELETE INVOICE',
            self::Restore     => 'RECVERY DELETED INVOICE',
            self::ForceDelete => 'PERMANENTLY DELETED INVOICE',
            self::Pay         => 'PAY INVOICE',
            self::Issue       => 'INVOICE APPROVAL|ISSUANCE (FINANCE)',
        };
    }

    public function group(): string
    {
        return 'invoices';
    }
}
