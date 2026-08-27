<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Appointment;
use App\Models\DentalRecord;
use App\Services\DentalRecord\DentalRecordService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DentalRecordServiceTest extends TestCase
{
    use RefreshDatabase;

    private DentalRecordService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DentalRecordService();
    }

    #[Test]
    public function create_record_creates_dental_record_without_xray(): void
    {
        $appointment = Appointment::factory()->create();

        $record = $this->service->createRecord([
            'tooth_number' => 5,
            'condition_type' => 'Cavity',
            'description' => 'Needs filling',
        ], $appointment);

        $this->assertInstanceOf(DentalRecord::class, $record);
        $this->assertSame($appointment->doctor_id, $record->doctor_id);
        $this->assertSame($appointment->patient_id, $record->patient_id);
        $this->assertSame($appointment->id, $record->appointment_id);
        $this->assertSame('Cavity', $record->condition_type);
        $this->assertNull($record->xray_image_path);
    }

    #[Test]
    public function create_record_uploads_xray_image(): void
    {
        Storage::fake('public');
        $appointment = Appointment::factory()->create();

        $file = UploadedFile::fake()->image('xray.jpg');

        $record = $this->service->createRecord([
            'tooth_number' => 5,
            'condition_type' => 'Cavity',
            'description' => 'Needs filling',
        ], $appointment, $file);

        $this->assertNotNull($record->xray_image_path);
        $this->assertStringContainsString('doctor/xrays', $record->xray_image_path);
    }

    #[Test]
    public function create_record_marks_appointment_as_completed(): void
    {
        $appointment = Appointment::factory()->create([
            'status' => \App\Enums\AppointmentStatus::Scheduled,
        ]);

        $this->service->createRecord([
            'tooth_number' => 5,
            'condition_type' => 'Cavity',
        ], $appointment);

        $this->assertSame('completed', $appointment->fresh()->status);
    }

    #[Test]
    public function update_record_modifies_existing_record(): void
    {
        $record = DentalRecord::factory()->create([
            'condition_type' => 'Healthy',
        ]);

        $updated = $this->service->updateRecord($record, [
            'condition_type' => 'Filled',
            'description' => 'Updated description',
        ]);

        $this->assertSame('Filled', $updated->condition_type);
        $this->assertSame('Updated description', $updated->description);
    }

    #[Test]
    public function update_record_uploads_new_xray(): void
    {
        Storage::fake('public');
        $record = DentalRecord::factory()->create(['xray_image_path' => 'doctor/xrays/old.jpg']);

        $file = UploadedFile::fake()->image('new_xray.jpg');

        $updated = $this->service->updateRecord($record, [
            'condition_type' => 'Filled',
        ], $file);

        $this->assertNotNull($updated->xray_image_path);
        $this->assertNotSame('doctor/xrays/old.jpg', $updated->xray_image_path);
    }

    #[Test]
    public function delete_record_removes_xray_file(): void
    {
        Storage::fake('public');
        $record = DentalRecord::factory()->create(['xray_image_path' => 'doctor/xrays/test.jpg']);
        Storage::disk('public')->put('doctor/xrays/test.jpg', 'test content');

        $this->service->deleteRecord($record);

        $this->assertSoftDeleted($record);
    }

    #[Test]
    public function delete_record_without_xray_does_not_fail(): void
    {
        $record = DentalRecord::factory()->create(['xray_image_path' => null]);

        $result = $this->service->deleteRecord($record);

        $this->assertTrue($result);
        $this->assertSoftDeleted($record);
    }
}
