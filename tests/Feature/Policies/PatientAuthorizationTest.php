<?php

declare(strict_types=1);

namespace Tests\Feature\Policies;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PatientAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    #[Test]
    public function admin_can_view_any_patient(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $patient = Patient::factory()->create();

        $this->assertTrue($admin->can('view', $patient));
    }

    #[Test]
    public function patient_can_view_own_profile(): void
    {
        $user = User::factory()->create();
        $user->assignRole('patient');
        $patient = Patient::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->can('view', $patient));
    }

    #[Test]
    public function patient_cannot_view_other_patients_profile(): void
    {
        $user = User::factory()->create();
        $user->assignRole('patient');
        $patient = Patient::factory()->create();

        $this->assertFalse($user->can('view', $patient));
    }

    #[Test]
    public function doctor_can_view_patient_with_appointment(): void
    {
        $doctor = Doctor::factory()->create();
        $doctor->user->assignRole('doctor');
        $patient = Patient::factory()->create();

        Appointment::factory()->create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
        ]);

        $policy = new \App\Policies\PatientPolicy();
        $this->assertTrue($policy->view($doctor->user, $patient));
    }

    #[Test]
    public function doctor_cannot_view_patient_without_appointment(): void
    {
        $doctor = Doctor::factory()->create();
        $doctor->user->assignRole('doctor');
        $patient = Patient::factory()->create();

        $policy = new \App\Policies\PatientPolicy();
        $this->assertFalse($policy->view($doctor->user, $patient));
    }

    #[Test]
    public function receptionist_can_view_any_patient(): void
    {
        $user = User::factory()->create();
        $user->assignRole('receptionist');
        $patient = Patient::factory()->create();

        $this->assertTrue($user->can('view', $patient));
    }

    #[Test]
    public function inactive_user_cannot_view_patient(): void
    {
        $user = User::factory()->create(['is_active' => false]);
        $user->assignRole('admin');
        $patient = Patient::factory()->create();

        $this->assertFalse($user->can('view', $patient));
    }
}
