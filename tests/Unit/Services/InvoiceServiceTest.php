<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\InvoiceStatus;
use App\Exceptions\BusinessRuleViolationException;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\PaymentService\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InvoiceServiceTest extends TestCase
{
    use RefreshDatabase;

    private InvoiceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new InvoiceService();
    }

    #[Test]
    public function create_invoice_creates_draft_invoice(): void
    {
        $appointment = Appointment::factory()->create();

        $invoice = $this->service->createInvoice([
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $appointment->doctor_id,
            'appointment_id' => $appointment->id,
        ]);

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertSame(InvoiceStatus::Draft, $invoice->status);
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id]);
    }

    #[Test]
    public function create_invoice_defaults_tax_and_discount_to_zero(): void
    {
        $appointment = Appointment::factory()->create();

        $invoice = $this->service->createInvoice([
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $appointment->doctor_id,
        ]);

        $this->assertSame(0.0, $invoice->tax_amount);
        $this->assertSame(0.0, $invoice->discount_amount);
    }

    #[Test]
    public function create_invoice_recalculates_totals(): void
    {
        $appointment = Appointment::factory()->create();

        $invoice = $this->service->createInvoice([
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $appointment->doctor_id,
            'appointment_id' => $appointment->id,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_name' => 'Service',
            'quantity' => 2,
            'unit_price' => 50.0,
        ]);

        $invoice->recalculateTotals();

        $invoice = $invoice->fresh();
        $this->assertSame(10000, (int) $invoice->getRawOriginal('sub_total'));
    }

    #[Test]
    public function upsert_for_appointment_creates_new_invoice_when_none_exists(): void
    {
        $appointment = Appointment::factory()->create();

        $invoice = $this->service->upsertForAppointment([
            'tax_amount' => 0,
            'discount_amount' => 0,
            'items' => [
                [
                    'item_name' => 'Cleaning',
                    'quantity' => 1,
                    'unit_price' => 100.0,
                ],
            ],
        ], $appointment);

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertSame(InvoiceStatus::Draft, $invoice->status);
        $this->assertCount(1, $invoice->items);
    }

    #[Test]
    public function upsert_for_appointment_updates_existing_invoice(): void
    {
        $appointment = Appointment::factory()->create();
        $invoice = Invoice::factory()->create(['appointment_id' => $appointment->id]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_name' => 'Old Service',
            'quantity' => 1,
            'unit_price' => 50.0,
        ]);

        $updated = $this->service->upsertForAppointment([
            'tax_amount' => 0,
            'discount_amount' => 0,
            'items' => [
                [
                    'item_name' => 'New Service',
                    'quantity' => 2,
                    'unit_price' => 30.0,
                ],
            ],
        ], $appointment);

        $updated->load('items');
        $this->assertCount(1, $updated->items);
        $this->assertSame('New Service', $updated->items->first()->item_name);
    }

    #[Test]
    public function sync_items_deletes_items_not_in_submission(): void
    {
        $invoice = Invoice::factory()->create();

        $item1 = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_name' => 'Service 1',
            'quantity' => 1,
            'unit_price' => 10.0,
        ]);
        $item2 = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_name' => 'Service 2',
            'quantity' => 1,
            'unit_price' => 20.0,
        ]);

        $this->service->syncItems($invoice, [
            ['id' => $item1->id, 'quantity' => 1, 'unit_price' => 10.0],
        ]);

        $this->assertDatabaseMissing('invoice_items', ['id' => $item2->id]);
        $this->assertDatabaseHas('invoice_items', ['id' => $item1->id]);
    }

    #[Test]
    public function record_payment_throws_when_payment_exceeds_due_amount(): void
    {
        $invoice = Invoice::factory()->create([
            'due_amount' => 5000,
            'status' => InvoiceStatus::Pending,
        ]);

        $this->expectException(BusinessRuleViolationException::class);
        $this->expectExceptionMessage('exceeds');

        $this->service->recordPayment($invoice, 6000);
    }

    #[Test]
    public function record_payment_succeeds_when_amount_within_due(): void
    {
        $invoice = Invoice::factory()->create([
            'tax_amount' => 0,
            'discount_amount' => 0,
            'paid_amount' => 20.0,
            'status' => InvoiceStatus::PartiallyPaid,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_name' => 'Service',
            'quantity' => 1,
            'unit_price' => 100.0,
        ]);
        $invoice->recalculateTotals();
        $invoice = $invoice->fresh();

        $result = $this->service->recordPayment($invoice, 30.0);

        $result = $result->fresh();
        $this->assertSame(50.0, $result->paid_amount);
        $this->assertSame(InvoiceStatus::PartiallyPaid, $result->status);
    }

    #[Test]
    public function record_payment_marks_invoice_paid_when_fully_paid(): void
    {
        $invoice = Invoice::factory()->create([
            'tax_amount' => 0,
            'discount_amount' => 0,
            'paid_amount' => 20.0,
            'status' => InvoiceStatus::PartiallyPaid,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_name' => 'Service',
            'quantity' => 1,
            'unit_price' => 100.0,
        ]);
        $invoice->recalculateTotals();
        $invoice = $invoice->fresh();

        $result = $this->service->recordPayment($invoice, 80.0);

        $result = $result->fresh();
        $this->assertSame(InvoiceStatus::Paid, $result->status);
    }
}
