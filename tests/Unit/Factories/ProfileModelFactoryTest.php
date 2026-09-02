<?php

declare(strict_types=1);

namespace Tests\Unit\Factories;

use App\Factories\Model\ProfileModelFactory;
use App\Models\Admin;
use App\Models\Doctor;
use App\Models\Financial;
use App\Models\Patient;
use App\Models\Receptionist;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ProfileModelFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        ProfileModelFactory::reset();
    }

    #[Test]
    public function resolve_class_returns_doctor_model(): void
    {
        $this->assertSame(Doctor::class, ProfileModelFactory::resolveClass('doctor'));
    }

    #[Test]
    public function resolve_class_returns_patient_model(): void
    {
        $this->assertSame(Patient::class, ProfileModelFactory::resolveClass('patient'));
    }

    #[Test]
    public function resolve_class_returns_receptionist_model(): void
    {
        $this->assertSame(Receptionist::class, ProfileModelFactory::resolveClass('receptionist'));
    }

    #[Test]
    public function resolve_class_uses_convention_for_unmapped_role(): void
    {
        $this->assertSame(Admin::class, ProfileModelFactory::resolveClass('admin'));
        $this->assertSame(Financial::class, ProfileModelFactory::resolveClass('financial'));
    }

    #[Test]
    public function resolve_class_is_case_insensitive(): void
    {
        $this->assertSame(Doctor::class, ProfileModelFactory::resolveClass('DOCTOR'));
        $this->assertSame(Doctor::class, ProfileModelFactory::resolveClass('Doctor'));
        $this->assertSame(Patient::class, ProfileModelFactory::resolveClass('PATIENT'));
    }

    #[Test]
    public function resolve_class_trims_whitespace(): void
    {
        $this->assertSame(Doctor::class, ProfileModelFactory::resolveClass('  doctor  '));
    }

    #[Test]
    public function resolve_class_throws_for_unknown_role(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Architectural Integrity Violation');

        ProfileModelFactory::resolveClass('unknown_role');
    }

    #[Test]
    public function register_mapping_adds_new_role_mapping(): void
    {
        ProfileModelFactory::registerMapping('custom_role', Doctor::class);

        $this->assertSame(Doctor::class, ProfileModelFactory::resolveClass('custom_role'));
    }

    #[Test]
    public function register_mapping_throws_for_non_existent_class(): void
    {
        $this->expectException(RuntimeException::class);

        ProfileModelFactory::registerMapping('bad_role', 'NonExistentModel');
    }

    #[Test]
    public function register_mapping_throws_for_invalid_model_class(): void
    {
        $this->expectException(RuntimeException::class);

        ProfileModelFactory::registerMapping('bad_role', \stdClass::class);
    }

    #[Test]
    public function register_mappings_registers_multiple_roles(): void
    {
        ProfileModelFactory::registerMappings([
            'custom1' => Doctor::class,
            'custom2' => Patient::class,
        ]);

        $this->assertSame(Doctor::class, ProfileModelFactory::resolveClass('custom1'));
        $this->assertSame(Patient::class, ProfileModelFactory::resolveClass('custom2'));
    }

    #[Test]
    public function set_custom_resolver_allows_dynamic_resolution(): void
    {
        ProfileModelFactory::setCustomResolver(static fn(string $role): ?string => $role === 'dynamic' ? Doctor::class : null);

        $this->assertSame(Doctor::class, ProfileModelFactory::resolveClass('dynamic'));
    }

    #[Test]
    public function reset_restores_default_mappings(): void
    {
        ProfileModelFactory::registerMapping('temp_role', Doctor::class);
        ProfileModelFactory::reset();

        $this->expectException(RuntimeException::class);
        ProfileModelFactory::resolveClass('temp_role');
    }
}
