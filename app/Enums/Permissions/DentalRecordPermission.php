<?php

declare(strict_types=1);

namespace App\Enums\Permissions;

use App\Contracts\Permissions\PermissionEnum;

enum DentalRecordPermission: string implements PermissionEnum
{
    case ViewAny     = 'dental_records.viewAny';
    case View        = 'dental_records.view';
    case Create      = 'dental_records.create';
    case Update      = 'dental_records.update';
    case Delete      = 'dental_records.delete';
    case Restore     = 'dental_records.restore';
    case ForceDelete = 'dental_records.forceDelete';

    public function label(): string
    {
        return match ($this) {
            self::ViewAny     => 'SHOW ALL DENTAL RECORDS',
            self::View        => 'SHOW MEDICAL HISTORY',
            self::Create      => 'CREATE MIDECAL HISTORY',
            self::Update      => 'EDIT MEDICAL HISTORY',
            self::Delete      => 'DELETE MEDICAL HISTORY',
            self::Restore     => 'RECOVERY DELETED MEDICAL HISTORY',
            self::ForceDelete => 'PERMANENTLY DELETE MEDICAL RECORD',
        };
    }

    public function group(): string
    {
        return 'dental_records';
    }
}
