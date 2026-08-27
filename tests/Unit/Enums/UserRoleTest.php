<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\UserRole;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class UserRoleTest extends TestCase
{
    #[Test]
    public function values_method_returns_all_role_values(): void
    {
        $values = UserRole::values();

        $this->assertSame(['admin', 'doctor', 'receptionist', 'patient', 'financial'], $values);
    }

    #[Test]
    public function cases_are_string_backed(): void
    {
        $this->assertSame('admin', UserRole::Admin->value);
        $this->assertSame('doctor', UserRole::Doctor->value);
        $this->assertSame('receptionist', UserRole::Receptionist->value);
        $this->assertSame('patient', UserRole::Patient->value);
        $this->assertSame('financial', UserRole::Financial->value);
    }

    #[Test]
    public function staff_roles_excludes_patient(): void
    {
        $staffRoles = UserRole::staffRoles();

        $this->assertNotContains(UserRole::Patient, $staffRoles);
    }

    #[Test]
    public function staff_roles_contains_all_non_patient_roles(): void
    {
        $staffRoles = UserRole::staffRoles();

        $this->assertContains(UserRole::Admin, $staffRoles);
        $this->assertContains(UserRole::Doctor, $staffRoles);
        $this->assertContains(UserRole::Receptionist, $staffRoles);
        $this->assertContains(UserRole::Financial, $staffRoles);
        $this->assertCount(4, $staffRoles);
    }

    #[Test]
    public function staff_role_values_returns_string_values(): void
    {
        $staffValues = UserRole::staffRoleValues();

        $this->assertSame(['admin', 'doctor', 'receptionist', 'financial'], $staffValues);
    }

    #[Test]
    #[DataProvider('labelProvider')]
    public function label_returns_correct_display_name(UserRole $role, string $expected): void
    {
        $this->assertSame($expected, $role->label());
    }

    public static function labelProvider(): Generator
    {
        yield 'admin' => [UserRole::Admin, 'SYSTEM ADMINISTRATION'];
        yield 'doctor' => [UserRole::Doctor, 'DOCTOR'];
        yield 'receptionist' => [UserRole::Receptionist, 'RECEPTIONIST EMPLOYEE'];
        yield 'patient' => [UserRole::Patient, 'PATIENT'];
        yield 'financial' => [UserRole::Financial, 'FINANTIAL EMPLOYEE'];
    }
}
