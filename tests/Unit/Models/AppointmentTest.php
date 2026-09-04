<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DentalRecord;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\TreatmentCourse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AppointmentTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function casts_date_fields(): void
    {
        $appointment = Appointment::factory()->create([
            'appointment_date' => '2025-01-15',
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
        ]);

        $this->assertEquals('2025-01-15', $appointment->appointment_date->format('Y-m-d'));
        $this->assertContains($appointment->status, AppointmentStatus::cases());
    }

    #[Test]
    public function casts_status_as_enum(): void
    {
        $appointment = Appointment::factory()->create(['status' => AppointmentStatus::Completed]);

        $this->assertEquals(AppointmentStatus::Completed, $appointment->status);
    }

    #[Test]
    public function casts_duration_minutes_as_integer(): void
    {
        $appointment = Appointment::factory()->create(['duration_minutes' => '45']);

        $this->assertSame(45, $appointment->duration_minutes);
    }

    #[Test]
    public function doctor_relationship(): void
    {
        $doctor = Doctor::factory()->create();
        $appointment = Appointment::factory()->create(['doctor_id' => $doctor->id]);

        $this->assertInstanceOf(Doctor::class, $appointment->doctor);
        $this->assertSame($doctor->id, $appointment->doctor->id);
    }

    #[Test]
    public function patient_relationship(): void
    {
        $patient = Patient::factory()->create();
        $appointment = Appointment::factory()->create(['patient_id' => $patient->id]);

        $this->assertInstanceOf(Patient::class, $appointment->patient);
        $this->assertSame($patient->id, $appointment->patient->id);
    }

    #[Test]
    public function invoice_relationship(): void
    {
        $appointment = Appointment::factory()->create();
        Invoice::factory()->create(['appointment_id' => $appointment->id]);

        $this->assertInstanceOf(Invoice::class, $appointment->invoice);
    }

    #[Test]
    public function dental_record_relationship(): void
    {
        $appointment = Appointment::factory()->create();
        DentalRecord::factory()->create(['appointment_id' => $appointment->id]);

        $this->assertInstanceOf(DentalRecord::class, $appointment->dentalRecord);
    }

    #[Test]
    public function treatment_course_relationship(): void
    {
        $course = TreatmentCourse::factory()->create();
        $appointment = Appointment::factory()->create(['treatment_course_id' => $course->id]);

        $this->assertInstanceOf(TreatmentCourse::class, $appointment->treatmentCourse);
    }

    #[Test]
    public function uses_soft_deletes(): void
    {
        $appointment = Appointment::factory()->create();
        $appointment->delete();

        $this->assertSoftDeleted($appointment);
    }
}
