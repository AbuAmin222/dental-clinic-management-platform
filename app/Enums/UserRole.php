<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Centerlization for system roles - The unique source for role names in whole project.
 *
 * The values here must combatable role names in RoleSeeder (roles.name)،
 * Because User::assignRole()/hasRole() searching string name.
 * Any edit here should make acceptable edit in RoleSeeder، والعكس صحيح.
 */
enum UserRole: string
{
    case Admin = 'admin';
    case Doctor = 'doctor';
    case Receptionist = 'receptionist';
    case Patient = 'patient';
    case Financial = 'financial';

    /**
     * Role that have salary (All employees unless patient).
     */
    public static function staffRoles(): array
    {
        return [self::Admin, self::Doctor, self::Receptionist, self::Financial];
    }

    /** @return string[] */
    public static function staffRoleValues(): array
    {
        return array_map(fn(self $role) => $role->value, self::staffRoles());
    }

    /** @return string[] All role values - for use in rules Validation (Rule::in) */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Admin        => 'SYSTEM ADMINISTRATION',
            self::Doctor        => 'DOCTOR',
            self::Receptionist  => 'RECEPTIONIST EMPLOYEE',
            self::Patient        => 'PATIENT',
            self::Financial      => 'FINANTIAL EMPLOYEE',
        };
    }
}
