<?php

declare(strict_types=1);

namespace Tests\Feature\Integration;

use App\Enums\AppointmentStatus;
use App\Enums\InvoiceStatus;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DentalRecord;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Patient;
use App\Models\Pricing;
use App\Models\User;
use App\Services\Appointment\AppointmentService;
use App\Services\DentalRecord\DentalRecordService;
use App\Services\PaymentService\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ClinicWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private AppointmentService $appointmentService;
    private InvoiceService $invoiceService;
    private DentalRecordService $dentalRecordService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->appointmentService = new AppointmentService();
        $this->invoiceService = new InvoiceService();
        $this->dentalRecordService = new DentalRecordService();
    }

    #[Test]
    public function full_appointment_to_invoice_to_record_workflow(): void
    {
        $doctor = Doctor::factory()->create();
        $patient = Patient::factory()->create();

        $appointment = $this->appointmentService->bookAppointment([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'appointment_date' => '2025-03-01',
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
            'reason_for_visit' => 'Routine checkup',
        ], $patient->id);

        $this->assertSame(AppointmentStatus::Scheduled, $appointment->status);

        Pricing::factory()->create([
            'doctor_id' => $doctor->id,
            'service_name' => 'Checkup',
            'amount' => 15000,
        ]);

        $invoice = $this->invoiceService->upsertForAppointment([
            'tax_amount' => 0,
            'discount_amount' => 0,
            'items' => [
                [
                    'item_name' => 'Checkup',
                    'quantity' => 1,
                    'unit_price' => 15000,
                ],
            ],
        ], $appointment);

        $this->assertSame(InvoiceStatus::Draft, $invoice->status);
        $this->assertSame(15000, $invoice->getRawOriginal('sub_total'));

        $invoice->transitionTo(InvoiceStatus::Pending);
        $this->assertSame(InvoiceStatus::Pending, $invoice->fresh()->status);

        $this->invoiceService->recordPayment($invoice, 15000);

        $invoice = $invoice->fresh();
        $this->assertSame(InvoiceStatus::Paid, $invoice->status);

        $record = $this->dentalRecordService->createRecord([
            'tooth_number' => 3,
            'condition_type' => 'Healthy',
            'description' => 'Routine checkup completed',
        ], $appointment);

        $this->assertSame(AppointmentStatus::Completed, $appointment->fresh()->status);
        $this->assertDatabaseHas('dental_records', [
            'appointment_id' => $appointment->id,
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'condition_type' => 'Healthy',
        ]);
    }

    #[Test]
    public function workflow_throws_on_schedule_conflict(): void
    {
        $doctor = Doctor::factory()->create();
        $patient = Patient::factory()->create();

        $this->appointmentService->bookAppointment([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'appointment_date' => '2025-03-01',
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
        ], $patient->id);

        $this->expectException(\App\Exceptions\BusinessRuleViolationException::class);

        $this->appointmentService->bookAppointment([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'appointment_date' => '2025-03-01',
            'start_time' => '10:15:00',
            'end_time' => '10:45:00',
        ], $patient->id);
    }

    #[Test]
    public function invoice_state_transitions_through_full_lifecycle(): void
    {
        $invoice = Invoice::factory()->create(['status' => InvoiceStatus::Draft]);

        $invoice->transitionTo(InvoiceStatus::Pending);
        $this->assertSame(InvoiceStatus::Pending->value, $invoice->fresh()->status);

        $invoice->transitionTo(InvoiceStatus::PartiallyPaid);
        $this->assertSame(InvoiceStatus::PartiallyPaid->value, $invoice->fresh()->status);

        $invoice->transitionTo(InvoiceStatus::Paid);
        $this->assertSame(InvoiceStatus::Paid->value, $invoice->fresh()->status);

        $invoice->transitionTo(InvoiceStatus::Refunded);
        $this->assertSame(InvoiceStatus::Refunded->value, $invoice->fresh()->status);
    }

    #[Test]
    public function invoice_cannot_be_issued_when_already_pending(): void
    {
        $invoice = Invoice::factory()->create(['status' => InvoiceStatus::Pending]);

        $this->expectException(\App\Exceptions\IllegalInvoiceStateTransitionException::class);

        $invoice->transitionTo(InvoiceStatus::Pending);
    }
}
