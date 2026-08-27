<?php

declare(strict_types=1);

namespace Tests\Unit\Strategies;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Strategies\Authorization\Patient\DoctorAuthorizationStrategy;
use App\Strategies\Authorization\Patient\PatientAuthorizationStrategy;
use App\Strategies\Authorization\Patient\ReceptionistAuthorizationStrategy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PatientAuthorizationStrategyTest extends TestCase
{
    use RefreshDatabase;

    private PatientAuthorizationStrategy $strategy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->strategy = new PatientAuthorizationStrategy();
    }

    #[Test]
    public function doctor_can_authorize_own_patient_with_appointment(): void
    {
        $doctor = Doctor::factory()->create();
        $patient = Patient::factory()->create();
        $user = $doctor->user;
        $user->assignRole('doctor');

        Appointment::factory()->create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
        ]);

        $strategy = new DoctorAuthorizationStrategy();

        $this->assertTrue($strategy->authorize($user, $patient));
    }

    #[Test]
    public function doctor_cannot_authorize_patient_without_appointment(): void
    {
        $doctor = Doctor::factory()->create();
        $patient = Patient::factory()->create();
        $user = $doctor->user;
        $user->assignRole('doctor');

        $strategy = new DoctorAuthorizationStrategy();

        $this->assertFalse($strategy->authorize($user, $patient));
    }

    #[Test]
    public function patient_can_only_view_own_profile(): void
    {
        $patientUser = User::factory()->create();
        $patientUser->assignRole('patient');
        $patient = Patient::factory()->create(['user_id' => $patientUser->id]);

        $otherPatient = Patient::factory()->create();

        $this->assertTrue($this->strategy->authorize($patientUser, $patient));
        $this->assertFalse($this->strategy->authorize($patientUser, $otherPatient));
    }

    #[Test]
    public function receptionist_can_authorize_any_patient(): void
    {
        $receptionist = User::factory()->create();
        $receptionist->assignRole('receptionist');
        $patient = Patient::factory()->create();

        $strategy = new ReceptionistAuthorizationStrategy();

        $this->assertTrue($strategy->authorize($receptionist, $patient));
    }

    #[Test]
    public function doctor_strategy_returns_false_for_non_doctor_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('patient');
        $patient = Patient::factory()->create();

        $strategy = new DoctorAuthorizationStrategy();

        $this->assertFalse($strategy->authorize($user, $patient));
    }
}
