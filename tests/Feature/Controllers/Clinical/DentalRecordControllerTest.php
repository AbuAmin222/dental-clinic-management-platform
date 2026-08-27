<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\Clinical;

use App\Enums\AppointmentStatus;
use App\Http\Requests\Doctor\StoreDentalRecordRequest;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DentalRecord;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DentalRecordControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    #[Test]
    public function create_displays_form_for_authorized_doctor(): void
    {
        $doctor = Doctor::factory()->create();
        $patient = Patient::factory()->create();
        $appointment = Appointment::factory()->create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
        ]);

        $response = $this->actingAs($doctor->user)->get("/doctor/dental-records/{$appointment->id}/create");

        $response->assertStatus(200);
    }

    #[Test]
    public function store_creates_dental_record_and_completes_appointment(): void
    {
        $doctor = Doctor::factory()->create();
        $patient = Patient::factory()->create();
        $appointment = Appointment::factory()->create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'status' => AppointmentStatus::Scheduled,
        ]);

        $response = $this->actingAs($doctor->user)->post("/doctor/dental-records/{$appointment->id}", [
            'tooth_number' => 5,
            'condition_type' => 'Cavity',
            'description' => 'Needs filling',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('dental_records', [
            'appointment_id' => $appointment->id,
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'condition_type' => 'Cavity',
        ]);
        $this->assertSame(AppointmentStatus::Completed, $appointment->fresh()->status);
    }

    #[Test]
    public function store_redirects_when_appointment_belongs_to_different_doctor(): void
    {
        $doctor = Doctor::factory()->create();
        $patient = Patient::factory()->create();
        $otherDoctor = Doctor::factory()->create();
        $appointment = Appointment::factory()->create([
            'doctor_id' => $otherDoctor->id,
            'patient_id' => $patient->id,
        ]);

        $response = $this->actingAs($doctor->user)->post("/doctor/dental-records/{$appointment->id}", [
            'tooth_number' => 5,
            'condition_type' => 'Cavity',
        ]);

        $response->assertStatus(403);
    }
}
