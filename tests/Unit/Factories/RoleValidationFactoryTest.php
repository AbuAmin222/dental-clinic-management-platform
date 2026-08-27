<?php

declare(strict_types=1);

namespace Tests\Unit\Factories;

use App\Contracts\Validation\RoleValidationRulesInterface;
use App\Factories\Validation\RoleValidationFactory;
use App\Strategies\Validation\DoctorValidationRules;
use App\Strategies\Validation\PatientValidationRules;
use App\Strategies\Validation\ReceptionistValidationRules;
use App\Strategies\Validation\FinancialValidationRules;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RoleValidationFactoryTest extends TestCase
{
    #[Test]
    public function make_returns_doctor_validation_rules(): void
    {
        $rules = RoleValidationFactory::make('doctor');

        $this->assertInstanceOf(DoctorValidationRules::class, $rules);
        $this->assertInstanceOf(RoleValidationRulesInterface::class, $rules);
    }

    #[Test]
    public function make_returns_patient_validation_rules(): void
    {
        $rules = RoleValidationFactory::make('patient');

        $this->assertInstanceOf(PatientValidationRules::class, $rules);
        $this->assertInstanceOf(RoleValidationRulesInterface::class, $rules);
    }

    #[Test]
    public function make_returns_receptionist_validation_rules(): void
    {
        $rules = RoleValidationFactory::make('receptionist');

        $this->assertInstanceOf(ReceptionistValidationRules::class, $rules);
        $this->assertInstanceOf(RoleValidationRulesInterface::class, $rules);
    }

    #[Test]
    public function make_returns_financial_validation_rules(): void
    {
        $rules = RoleValidationFactory::make('financial');

        $this->assertInstanceOf(FinancialValidationRules::class, $rules);
        $this->assertInstanceOf(RoleValidationRulesInterface::class, $rules);
    }

    #[Test]
    public function make_throws_for_nonexistent_role(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Architecture error');

        RoleValidationFactory::make('nonexistent');
    }
}
