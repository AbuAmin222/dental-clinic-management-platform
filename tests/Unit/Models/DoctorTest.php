<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Appointment;
use App\Models\DentalRecord;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Pricing;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DoctorTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function casts_experience_years_as_integer(): void
    {
        $doctor = Doctor::factory()->create(['experience_years' => '15']);

        $this->assertSame(15, $doctor->experience_years);
    }

    #[Test]
    public function uses_soft_deletes(): void
    {
        $doctor = Doctor::factory()->create();
        $doctor->delete();

        $this->assertSoftDeleted($doctor);
    }

    #[Test]
    public function user_relationship(): void
    {
        $doctor = Doctor::factory()->create();

        $this->assertInstanceOf(User::class, $doctor->user);
    }

    #[Test]
    public function specialization_relationship(): void
    {
        $specialization = Specialization::factory()->create();
        $doctor = Doctor::factory()->create(['specialization_id' => $specialization->id]);

        $this->assertInstanceOf(Specialization::class, $doctor->specialization);
        $this->assertSame($specialization->id, $doctor->specialization->id);
    }

    #[Test]
    public function appointments_relationship(): void
    {
        $doctor = Doctor::factory()->create();
        Appointment::factory()->create(['doctor_id' => $doctor->id]);

        $this->assertCount(1, $doctor->appointments);
    }

    #[Test]
    public function dental_records_relationship(): void
    {
        $doctor = Doctor::factory()->create();
        DentalRecord::factory()->create(['doctor_id' => $doctor->id]);

        $this->assertCount(1, $doctor->dentalRecords);
    }

    #[Test]
    public function pricings_relationship(): void
    {
        $doctor = Doctor::factory()->create();
        Pricing::factory()->create(['doctor_id' => $doctor->id]);

        $this->assertCount(1, $doctor->pricings);
    }

    #[Test]
    public function invoices_relationship(): void
    {
        $doctor = Doctor::factory()->create();
        Invoice::factory()->create(['doctor_id' => $doctor->id]);

        $this->assertCount(1, $doctor->invoices);
    }

    #[Test]
    public function fillable_attributes(): void
    {
        $fillable = (new Doctor())->getFillable();

        $this->assertContains('user_id', $fillable);
        $this->assertContains('specialization_id', $fillable);
        $this->assertContains('license_number', $fillable);
        $this->assertContains('bio', $fillable);
        $this->assertContains('experience_years', $fillable);
    }
}
