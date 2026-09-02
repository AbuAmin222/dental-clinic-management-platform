<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Doctor;
use App\Models\DentalChart;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DentalChartTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function casts_tooth_number_as_integer(): void
    {
        $chart = DentalChart::create([
            'patient_id' => Patient::factory()->create()->id,
            'doctor_id' => Doctor::factory()->create()->id,
            'tooth_number' => '15',
            'condition' => 'Healthy',
        ]);

        $this->assertSame(15, $chart->tooth_number);
    }

    #[Test]
    public function upsert_for_tooth_creates_new_record(): void
    {
        $patientId = Patient::factory()->create()->id;
        $doctorId = Doctor::factory()->create()->id;

        $chart = DentalChart::upsertForTooth($patientId, $doctorId, 5, 'Cavity', 'Needs filling');

        $this->assertDatabaseHas('dental_charts', [
            'patient_id' => $patientId,
            'tooth_number' => 5,
            'condition' => 'Cavity',
        ]);
    }

    #[Test]
    public function upsert_for_tooth_updates_existing_record(): void
    {
        $patientId = Patient::factory()->create()->id;
        $doctorId = Doctor::factory()->create()->id;

        DentalChart::upsertForTooth($patientId, $doctorId, 5, 'Cavity', 'Old notes');

        $updated = DentalChart::upsertForTooth($patientId, $doctorId, 5, 'Filled', 'New notes');

        $this->assertDatabaseCount('dental_charts', 1);
        $this->assertSame('Filled', $updated->fresh()->condition);
        $this->assertSame('New notes', $updated->fresh()->notes);
    }

    #[Test]
    public function upsert_for_tooth_changes_doctor(): void
    {
        $patientId = Patient::factory()->create()->id;
        $doctor1 = Doctor::factory()->create();
        $doctor2 = Doctor::factory()->create();

        DentalChart::upsertForTooth($patientId, $doctor1->id, 3, 'Healthy');
        DentalChart::upsertForTooth($patientId, $doctor2->id, 3, 'Cavity');

        $chart = DentalChart::where('patient_id', $patientId)->where('tooth_number', 3)->first();

        $this->assertSame($doctor2->id, $chart->doctor_id);
    }

    #[Test]
    public function patient_relationship(): void
    {
        $chart = DentalChart::factory()->create();

        $this->assertInstanceOf(Patient::class, $chart->patient);
    }

    #[Test]
    public function doctor_relationship(): void
    {
        $chart = DentalChart::factory()->create();

        $this->assertInstanceOf(Doctor::class, $chart->doctor);
    }
}
