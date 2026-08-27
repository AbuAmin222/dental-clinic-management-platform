<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Dashboard;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\User;
use App\Services\Dashboard\DoctorDashboardTelemetry;
use App\Services\Dashboard\PatientDashboardTelemetry;
use App\Services\Dashboard\ReceptionistDashboardTelemetry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DashboardTelemetryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    #[Test]
    public function receptionist_telemetry_returns_metrics(): void
    {
        $this->seed(\Database\Seeders\AppointmentSeeder::class);

        $telemetry = new ReceptionistDashboardTelemetry();
        $user = User::factory()->create();
        $user->assignRole('receptionist');

        $result = $telemetry->getTelemetry($user);

        $this->assertArrayHasKey('metrics', $result);
        $this->assertArrayHasKey('active_appointments', $result['metrics']);
        $this->assertArrayHasKey('pending_collections', $result['metrics']);
        $this->assertArrayHasKey('total_patients', $result['metrics']);
        $this->assertSame('Receptionist/Dashboard', $result['view_path']);
    }

    #[Test]
    public function doctor_telemetry_returns_metrics_for_valid_doctor(): void
    {
        $telemetry = new DoctorDashboardTelemetry();
        $doctor = \App\Models\Doctor::factory()->create();
        $doctor->user->assignRole('doctor');

        $result = $telemetry->getTelemetry($doctor->user);

        $this->assertArrayHasKey('metrics', $result);
        $this->assertArrayHasKey('today_appointments', $result['metrics']);
        $this->assertArrayHasKey('total_consultations', $result['metrics']);
        $this->assertSame('Doctor/Dashboard', $result['view_path']);
    }

    #[Test]
    public function doctor_telemetry_returns_empty_metrics_without_doctor_profile(): void
    {
        $telemetry = new DoctorDashboardTelemetry();
        $user = User::factory()->create();
        $user->assignRole('doctor');

        $result = $telemetry->getTelemetry($user);

        $this->assertSame([], $result['metrics']);
        $this->assertSame('Doctor/Dashboard', $result['view_path']);
    }

    #[Test]
    public function patient_telemetry_returns_patient_data(): void
    {
        $telemetry = new PatientDashboardTelemetry();
        $patient = Patient::factory()->create();
        $patient->user->assignRole('patient');

        $result = $telemetry->getTelemetry($patient->user);

        $this->assertArrayHasKey('patient', $result);
        $this->assertArrayHasKey('metrics', $result);
        $this->assertArrayHasKey('total_appointments', $result['metrics']);
        $this->assertSame('Patient/Dashboard', $result['view_path']);
    }

    #[Test]
    public function patient_telemetry_returns_zero_count_for_new_patient(): void
    {
        $telemetry = new PatientDashboardTelemetry();
        $patient = Patient::factory()->create();
        $patient->user->assignRole('patient');

        $result = $telemetry->getTelemetry($patient->user);

        $this->assertSame(0, $result['metrics']['total_appointments']);
    }
}
