<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\Doctor;

use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DoctorDashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    #[Test]
    public function index_displays_todays_appointments_for_doctor(): void
    {
        $doctor = Doctor::factory()->create();
        $patient = Patient::factory()->create();

        Appointment::factory()->create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'appointment_date' => now()->toDateString(),
            'status' => \App\Enums\AppointmentStatus::Confirmed,
        ]);

        $response = $this->actingAs($doctor->user)->get('/doctor/dashboard');

        $response->assertStatus(200);
    }

    #[Test]
    public function index_returns_404_when_doctor_profile_not_found(): void
    {
        $user = User::factory()->create();
        $user->assignRole('doctor');

        $response = $this->actingAs($user)->get('/doctor/dashboard');

        $response->assertNotFound();
    }

    #[Test]
    public function index_returns_403_for_non_doctor(): void
    {
        $user = User::factory()->create();
        $user->assignRole('financial');

        $response = $this->actingAs($user)->get('/doctor/dashboard');

        $response->assertStatus(403);
    }
}
