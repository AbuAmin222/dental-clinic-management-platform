<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Administrator privilege level — distinguishes a "Super Admin" from a regular Admin.
 *
 * Practical difference: `SuperAdmin` is structurally protected from deletion/reduction/removal of their Admin role (see * UserPolicy and Admin\UserRoleController*) — a safeguard against a complete system lock-up if the last Admin account is deleted.
 * An Admin account exists by mistake. A regular Admin (`Admin`) is subject to the same restrictions as any other account.
 */
enum AdminAccessLevel: string
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';

    /** @return string[] */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Root manager (full powers protected)',
            self::Admin      => 'System administrator',
        };
    }
}
