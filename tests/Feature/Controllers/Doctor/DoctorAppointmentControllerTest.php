<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\Doctor;

use App\Enums\AppointmentStatus;
use App\Models\Doctor;
use App\Models\DentalRecord;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\TreatmentCourse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DoctorAppointmentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    #[Test]
    public function index_returns_appointments_for_authorized_doctor(): void
    {
        $doctor = Doctor::factory()->create();
        $patient = Patient::factory()->create();
        Appointment::factory()->create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'reason_for_visit' => 'Checkup',
        ]);

        $response = $this->actingAs($doctor->user)->get('/doctor/appointments');

        $response->assertStatus(200);
    }

    #[Test]
    public function index_returns_404_when_doctor_profile_missing(): void
    {
        $user = User::factory()->create();
        $user->assignRole('doctor');

        $response = $this->actingAs($user)->get('/doctor/appointments');

        $response->assertNotFound();
    }

    #[Test]
    public function index_returns_403_for_non_doctor_user(): void
    {
        $user = User::factory()->create();
        $user->assignRole('financial');

        $response = $this->actingAs($user)->get('/doctor/appointments');

        $response->assertStatus(403);
    }
}
