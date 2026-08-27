<?php

declare(strict_types=1);

namespace Tests\Unit\Factories;

use App\Contracts\Telemetry\DashboardTelemetryInterface;
use App\Factories\Telemetry\DashboardTelemetryFactory;
use App\Services\Dashboard\DoctorDashboardTelemetry;
use App\Services\Dashboard\FinancialDashboardTelemetry;
use App\Services\Dashboard\PatientDashboardTelemetry;
use App\Services\Dashboard\ReceptionistDashboardTelemetry;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DashboardTelemetryFactoryTest extends TestCase
{
    #[Test]
    public function make_returns_doctor_dashboard_telemetry(): void
    {
        $telemetry = DashboardTelemetryFactory::make('doctor');

        $this->assertInstanceOf(DoctorDashboardTelemetry::class, $telemetry);
        $this->assertInstanceOf(DashboardTelemetryInterface::class, $telemetry);
    }

    #[Test]
    public function make_returns_patient_dashboard_telemetry(): void
    {
        $telemetry = DashboardTelemetryFactory::make('patient');

        $this->assertInstanceOf(PatientDashboardTelemetry::class, $telemetry);
        $this->assertInstanceOf(DashboardTelemetryInterface::class, $telemetry);
    }

    #[Test]
    public function make_returns_receptionist_dashboard_telemetry(): void
    {
        $telemetry = DashboardTelemetryFactory::make('receptionist');

        $this->assertInstanceOf(ReceptionistDashboardTelemetry::class, $telemetry);
        $this->assertInstanceOf(DashboardTelemetryInterface::class, $telemetry);
    }

    #[Test]
    public function make_returns_financial_dashboard_telemetry(): void
    {
        $telemetry = DashboardTelemetryFactory::make('financial');

        $this->assertInstanceOf(FinancialDashboardTelemetry::class, $telemetry);
        $this->assertInstanceOf(DashboardTelemetryInterface::class, $telemetry);
    }

    #[Test]
    public function make_is_case_insensitive(): void
    {
        $telemetry = DashboardTelemetryFactory::make('DOCTOR');

        $this->assertInstanceOf(DoctorDashboardTelemetry::class, $telemetry);
    }

    #[Test]
    public function make_throws_for_unknown_role(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Telemetry structural error');

        DashboardTelemetryFactory::make('unknown_role');
    }
}
