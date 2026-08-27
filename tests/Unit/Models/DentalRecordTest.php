<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DentalRecord;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DentalRecordTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function uses_soft_deletes(): void
    {
        $record = DentalRecord::factory()->create();
        $record->delete();

        $this->assertSoftDeleted($record);
    }

    #[Test]
    public function doctor_relationship(): void
    {
        $doctor = Doctor::factory()->create();
        $record = DentalRecord::factory()->create(['doctor_id' => $doctor->id]);

        $this->assertInstanceOf(Doctor::class, $record->doctor);
    }

    #[Test]
    public function patient_relationship(): void
    {
        $patient = Patient::factory()->create();
        $record = DentalRecord::factory()->create(['patient_id' => $patient->id]);

        $this->assertInstanceOf(Patient::class, $record->patient);
    }

    #[Test]
    public function appointment_relationship(): void
    {
        $appointment = Appointment::factory()->create();
        $record = DentalRecord::factory()->create(['appointment_id' => $appointment->id]);

        $this->assertInstanceOf(Appointment::class, $record->appointment);
    }

    #[Test]
    public function xray_url_returns_fallback_when_no_image_path(): void
    {
        $record = DentalRecord::factory()->create(['xray_image_path' => null]);

        $url = $record->xray_url;

        $this->assertStringContainsString('placehold.co', $url);
    }

    #[Test]
    public function xray_url_returns_storage_url_when_path_exists(): void
    {
        $record = DentalRecord::factory()->create(['xray_image_path' => 'doctor/xrays/test.jpg']);

        $url = $record->xray_url;

        $this->assertNotEmpty($url);
    }

    #[Test]
    public function fillable_attributes(): void
    {
        $fillable = (new DentalRecord())->getFillable();

        $this->assertContains('doctor_id', $fillable);
        $this->assertContains('patient_id', $fillable);
        $this->assertContains('appointment_id', $fillable);
        $this->assertContains('tooth_number', $fillable);
        $this->assertContains('condition_type', $fillable);
        $this->assertContains('description', $fillable);
        $this->assertContains('xray_image_path', $fillable);
    }
}
