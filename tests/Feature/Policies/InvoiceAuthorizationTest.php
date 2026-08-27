<?php

declare(strict_types=1);

namespace Tests\Feature\Policies;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InvoiceAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    #[Test]
    public function admin_can_approve_invoice(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $invoice = Invoice::factory()->create();
        $appointment = Appointment::factory()->create();

        $this->assertTrue($admin->can('approve', [$invoice, $appointment]));
    }

    #[Test]
    public function admin_can_pay_invoice(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $invoice = Invoice::factory()->create();
        $appointment = Appointment::factory()->create();

        $this->assertTrue($admin->can('pay', [$invoice, $appointment]));
    }

    #[Test]
    public function financial_can_approve_invoice(): void
    {
        $financial = User::factory()->create();
        $financial->assignRole('financial');

        $invoice = Invoice::factory()->create();
        $appointment = Appointment::factory()->create();

        $policy = new \App\Policies\InvoicePolicy();
        $this->assertTrue($policy->approve($financial, $invoice, $appointment));
    }

    #[Test]
    public function doctor_cannot_approve_invoice(): void
    {
        $financial = User::factory()->create();
        $financial->assignRole('financial');
        $doctor = Doctor::factory()->create();
        $doctor->user->assignRole('doctor');

        $invoice = Invoice::factory()->create();
        $appointment = Appointment::factory()->create();

        $policy = new \App\Policies\InvoicePolicy();
        $this->assertFalse($policy->approve($doctor->user, $invoice, $appointment));
    }

    #[Test]
    public function patient_can_pay_own_invoice(): void
    {
        $patient = Patient::factory()->create();
        $patient->user->assignRole('patient');

        $invoice = Invoice::factory()->create([
            'patient_id' => $patient->id,
        ]);
        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->id,
        ]);

        $policy = new \App\Policies\InvoicePolicy();
        $this->assertTrue($policy->pay($patient->user, $invoice, $appointment));
    }

    #[Test]
    public function patient_cannot_pay_other_patients_invoice(): void
    {
        $patient = Patient::factory()->create();
        $patient->user->assignRole('patient');

        $otherPatient = Patient::factory()->create();

        $invoice = Invoice::factory()->create([
            'patient_id' => $otherPatient->id,
        ]);
        $appointment = Appointment::factory()->create([
            'patient_id' => $otherPatient->id,
        ]);

        $policy = new \App\Policies\InvoicePolicy();
        $this->assertFalse($policy->pay($patient->user, $invoice, $appointment));
    }
}
