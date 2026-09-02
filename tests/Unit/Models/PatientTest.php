<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\BloodGroup;
use App\Models\Appointment;
use App\Models\DentalRecord;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PatientTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function casts_blood_group_as_enum(): void
    {
        $patient = Patient::factory()->create(['blood_group' => 'O+']);

        $this->assertSame('O+', $patient->blood_group);
    }

    #[Test]
    public function user_relationship(): void
    {
        $patient = Patient::factory()->create();

        $this->assertInstanceOf(User::class, $patient->user);
    }

    #[Test]
    public function appointments_relationship(): void
    {
        $patient = Patient::factory()->create();
        Appointment::factory()->create(['patient_id' => $patient->id]);

        $this->assertCount(1, $patient->appointments);
    }

    #[Test]
    public function invoices_relationship(): void
    {
        $patient = Patient::factory()->create();
        Invoice::factory()->create(['patient_id' => $patient->id]);

        $this->assertCount(1, $patient->invoices);
    }

    #[Test]
    public function dental_records_relationship(): void
    {
        $patient = Patient::factory()->create();
        DentalRecord::factory()->create(['patient_id' => $patient->id]);

        $this->assertCount(1, $patient->dentalRecords);
    }

    #[Test]
    public function fillable_attributes(): void
    {
        $fillable = (new Patient())->getFillable();

        $this->assertContains('user_id', $fillable);
        $this->assertContains('blood_group', $fillable);
        $this->assertContains('allergies', $fillable);
        $this->assertContains('chronic_diseases', $fillable);
        $this->assertContains('emergency_contact_name', $fillable);
        $this->assertContains('emergency_contact_phone', $fillable);
        $this->assertContains('medical_notes', $fillable);
    }
}
