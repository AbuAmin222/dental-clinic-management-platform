<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\AppointmentStatus;
use App\Exceptions\BusinessRuleViolationException;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Services\Appointment\AppointmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AppointmentServiceTest extends TestCase
{
    use RefreshDatabase;

    private AppointmentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AppointmentService();
    }

    #[Test]
    public function book_appointment_creates_appointment_with_scheduled_status(): void
    {
        $doctor = Doctor::factory()->create();
        $patient = Patient::factory()->create();

        $appointment = $this->service->bookAppointment([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'appointment_date' => '2025-02-01',
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
        ], $patient->id);

        $this->assertInstanceOf(Appointment::class, $appointment);
        $this->assertSame(AppointmentStatus::Confirmed, $appointment->status);
        $this->assertSame($doctor->id, $appointment->doctor_id);
        $this->assertSame($patient->id, $appointment->patient_id);
    }

    #[Test]
    public function book_appointment_throws_on_overlapping_schedule(): void
    {
        $doctor = Doctor::factory()->create();
        $patient = Patient::factory()->create();

        $this->service->bookAppointment([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'appointment_date' => '2025-02-01',
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
        ], $patient->id);

        $this->expectException(BusinessRuleViolationException::class);
        $this->expectExceptionMessage('unavailable');

        $this->service->bookAppointment([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'appointment_date' => '2025-02-01',
            'start_time' => '10:15:00',
            'end_time' => '10:45:00',
        ], $patient->id);
    }

    #[Test]
    public function book_appointment_throws_for_nonexistent_doctor(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->service->bookAppointment([
            'doctor_id' => 999,
            'patient_id' => 1,
            'appointment_date' => '2025-02-01',
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
        ], 1);
    }

    #[Test]
    public function book_appointment_does_not_throw_when_cancelled_appointment_overlaps(): void
    {
        $doctor = Doctor::factory()->create();
        $patient = Patient::factory()->create();

        $this->service->bookAppointment([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'appointment_date' => '2025-02-01',
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
        ], $patient->id);

        Appointment::query()->update(['status' => AppointmentStatus::Cancelled]);

        $newAppointment = $this->service->bookAppointment([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'appointment_date' => '2025-02-01',
            'start_time' => '10:15:00',
            'end_time' => '10:45:00',
        ], $patient->id);

        $this->assertNotNull($newAppointment);
    }

    #[Test]
    public function book_appointment_defaults_end_time_when_not_provided(): void
    {
        $doctor = Doctor::factory()->create();
        $patient = Patient::factory()->create();

        $appointment = $this->service->bookAppointment([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'appointment_date' => '2025-02-01',
            'start_time' => '10:00:00',
        ], $patient->id);

        $this->assertSame('10:30:00', $appointment->end_time);
        $this->assertSame(30, $appointment->duration_minutes);
    }

    #[Test]
    public function update_appointment_changes_details(): void
    {
        $doctor = Doctor::factory()->create();
        $patient = Patient::factory()->create();
        $appointment = Appointment::factory()->create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
        ]);

        $updated = $this->service->updateAppointment($appointment, [
            'reason_for_visit' => 'Updated reason',
        ]);

        $this->assertSame('Updated reason', $updated->reason_for_visit);
    }

    #[Test]
    public function update_appointment_throws_on_reschedule_conflict(): void
    {
        $doctor = Doctor::factory()->create();
        $patient = Patient::factory()->create();
        $otherPatient = Patient::factory()->create();

        $existing = $this->service->bookAppointment([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'appointment_date' => '2025-02-01',
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
        ], $patient->id);

        $this->expectException(BusinessRuleViolationException::class);
        $this->expectExceptionMessage('already booked');

        $this->service->updateAppointment($existing, [
            'doctor_id' => $doctor->id,
            'appointment_date' => '2025-02-01',
            'start_time' => '10:15:00',
            'end_time' => '10:45:00',
        ]);
    }

    #[Test]
    public function cancel_appointment_sets_cancelled_status(): void
    {
        $doctor = Doctor::factory()->create();
        $patient = Patient::factory()->create();
        $appointment = Appointment::factory()->create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'status' => AppointmentStatus::Scheduled,
        ]);

        $result = $this->service->cancelAppointment($appointment);

        $this->assertTrue($result);
        $this->assertSame(AppointmentStatus::Cancelled, $appointment->fresh()->status);
    }
}
