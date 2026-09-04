<?php

declare(strict_types=1);

namespace App\Enums\Permissions;

use App\Contracts\Permissions\PermissionEnum;

/**
 * Single point of truth listing every domain Permission enum in the project.
 * Adding a new domain = add one line here + create its enum file.
 * PermissionSeeder never needs to change again.
 */
final class PermissionRegistry
{
    /** @return class-string<PermissionEnum>[] */
    public static function enums(): array
    {
        return [
            AppointmentPermission::class,
            DentalRecordPermission::class,
            InvoicePermission::class,
            PricingPermission::class,
            PatientPermission::class,
            UserPermission::class,
            SalaryPaymentPermission::class,
            LocalPaymentMethodPermission::class,
            SystemPermission::class,
        ];
    }

    /** @return PermissionEnum[] Flat list of every permission case across every domain. */
    public static function all(): array
    {
        return array_merge(
            ...array_map(static fn(string $enum): array => $enum::cases(), self::enums())
        );
    }
}
