<?php

declare(strict_types=1);

namespace Tests\Unit\Factories;

use App\Contracts\Profile\RoleProfileStrategyInterface;
use App\Factories\Profile\RoleProfileFactory;
use App\Strategies\Profile\DoctorProfileStrategy;
use App\Strategies\Profile\FinancialProfileStrategy;
use App\Strategies\Profile\PatientProfileStrategy;
use App\Strategies\Profile\ReceptionistProfileStrategy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RoleProfileFactoryTest extends TestCase
{
    use RefreshDatabase;
    #[Test]
    public function make_returns_doctor_profile_strategy(): void
    {
        $strategy = RoleProfileFactory::make('doctor');

        $this->assertInstanceOf(DoctorProfileStrategy::class, $strategy);
        $this->assertInstanceOf(RoleProfileStrategyInterface::class, $strategy);
    }

    #[Test]
    public function make_returns_patient_profile_strategy(): void
    {
        $strategy = RoleProfileFactory::make('patient');

        $this->assertInstanceOf(PatientProfileStrategy::class, $strategy);
        $this->assertInstanceOf(RoleProfileStrategyInterface::class, $strategy);
    }

    #[Test]
    public function make_returns_receptionist_profile_strategy(): void
    {
        $strategy = RoleProfileFactory::make('receptionist');

        $this->assertInstanceOf(ReceptionistProfileStrategy::class, $strategy);
        $this->assertInstanceOf(RoleProfileStrategyInterface::class, $strategy);
    }

    #[Test]
    public function make_returns_financial_profile_strategy(): void
    {
        $strategy = RoleProfileFactory::make('financial');

        $this->assertInstanceOf(FinancialProfileStrategy::class, $strategy);
        $this->assertInstanceOf(RoleProfileStrategyInterface::class, $strategy);
    }

    #[Test]
    public function make_is_case_insensitive(): void
    {
        $strategy = RoleProfileFactory::make('DOCTOR');

        $this->assertInstanceOf(DoctorProfileStrategy::class, $strategy);
    }

    #[Test]
    public function make_throws_for_unknown_role(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Architecture error');

        RoleProfileFactory::make('unknown_role');
    }
}
