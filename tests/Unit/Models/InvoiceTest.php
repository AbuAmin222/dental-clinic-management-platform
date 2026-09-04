<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Casts\MoneyCast;
use App\Enums\InvoiceStatus;
use App\Exceptions\IllegalInvoiceStateTransitionException;
use App\Models\Appointment;
use App\Models\DentalRecord;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Patient;
use App\States\Invoice\DraftState;
use App\States\Invoice\InvoiceStateFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    #[Test]
    public function casts_status_as_enum(): void
    {
        $invoice = Invoice::factory()->create(['status' => InvoiceStatus::Draft]);

        $this->assertEquals(InvoiceStatus::Draft, $invoice->status);
        $this->assertInstanceOf(InvoiceStatus::class, $invoice->status);
    }

    #[Test]
    public function casts_money_fields_with_money_cast(): void
    {
        $invoice = Invoice::factory()->create([
            'sub_total' => 10.0,
            'tax_amount' => 0.50,
            'discount_amount' => 0.25,
            'total_amount' => 10.25,
            'paid_amount' => 5.0,
            'due_amount' => 5.25,
            'balance_amount' => 5.25,
        ]);

        $this->assertSame(10.0, $invoice->sub_total);
        $this->assertSame(0.5, $invoice->tax_amount);
        $this->assertSame(0.25, $invoice->discount_amount);
        $this->assertSame(5.00, $invoice->paid_amount);
        $this->assertSame(5.25, $invoice->due_amount);
    }

    #[Test]
    public function state_method_returns_correct_state_class(): void
    {
        $invoice = Invoice::factory()->create(['status' => InvoiceStatus::Draft]);

        $this->assertInstanceOf(DraftState::class, $invoice->state());
    }

    #[Test]
    public function transition_to_valid_status(): void
    {
        $invoice = Invoice::factory()->create(['status' => InvoiceStatus::Draft]);

        $invoice->transitionTo(InvoiceStatus::Pending);

        $this->assertSame(InvoiceStatus::Pending, $invoice->fresh()->status);
    }

    #[Test]
    public function transition_throws_on_illegal_transition(): void
    {
        $invoice = Invoice::factory()->create(['status' => InvoiceStatus::Draft]);

        $this->expectException(IllegalInvoiceStateTransitionException::class);

        $invoice->transitionTo(InvoiceStatus::Refunded);
    }

    #[Test]
    public function transition_throws_from_cancelled_state(): void
    {
        $invoice = Invoice::factory()->create(['status' => InvoiceStatus::Cancelled]);

        $this->expectException(IllegalInvoiceStateTransitionException::class);

        $invoice->transitionTo(InvoiceStatus::Paid);
    }

    #[Test]
    public function transition_throws_from_refunded_state(): void
    {
        $invoice = Invoice::factory()->create(['status' => InvoiceStatus::Refunded]);

        $this->expectException(IllegalInvoiceStateTransitionException::class);

        $invoice->transitionTo(InvoiceStatus::Pending);
    }

    #[Test]
    public function record_payment_marks_invoice_paid(): void
    {
        $invoice = Invoice::factory()->create([
            'tax_amount' => 0,
            'discount_amount' => 0,
            'paid_amount' => 0.0,
            'status' => InvoiceStatus::Pending,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_name' => 'Service',
            'quantity' => 1,
            'unit_price' => 100.0,
        ]);

        $invoice->recalculateTotals();
        $invoice = $invoice->fresh();

        $invoice->recordPayment(100.0);

        $invoice = $invoice->fresh();
        $this->assertSame(InvoiceStatus::Paid, $invoice->status);
        $this->assertSame(100.0, $invoice->paid_amount);
        $this->assertSame(0.0, $invoice->due_amount);
    }

    #[Test]
    public function record_payment_marks_partial_payment(): void
    {
        $invoice = Invoice::factory()->create([
            'tax_amount' => 0,
            'discount_amount' => 0,
            'paid_amount' => 0.0,
            'status' => InvoiceStatus::Pending,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_name' => 'Service',
            'quantity' => 1,
            'unit_price' => 100.0,
        ]);

        $invoice->recalculateTotals();
        $invoice = $invoice->fresh();

        $invoice->recordPayment(50.0);

        $invoice = $invoice->fresh();
        $this->assertSame(InvoiceStatus::PartiallyPaid, $invoice->status);
        $this->assertSame(50.0, $invoice->paid_amount);
        $this->assertSame(50.0, $invoice->due_amount);
    }

    #[Test]
    public function recalculate_totals_from_items(): void
    {
        $invoice = Invoice::factory()->create([
            'tax_amount' => 0,
            'discount_amount' => 0,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_name' => 'Service 1',
            'quantity' => 2,
            'unit_price' => 50.0,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_name' => 'Service 2',
            'quantity' => 1,
            'unit_price' => 30.0,
        ]);

        $invoice->recalculateTotals();
        $invoice = $invoice->fresh();

        $this->assertSame(13000, (int) $invoice->getRawOriginal('sub_total'));
    }

    #[Test]
    public function recalculate_totals_applies_tax_and_discount(): void
    {
        $invoice = Invoice::factory()->create([
            'tax_amount' => 10.0,
            'discount_amount' => 5.0,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_name' => 'Service',
            'quantity' => 1,
            'unit_price' => 50.0,
        ]);

        $invoice->recalculateTotals();
        $invoice = $invoice->fresh();

        $this->assertSame(5500, (int) $invoice->getRawOriginal('total_amount'));
    }

    #[Test]
    public function uses_soft_deletes(): void
    {
        $invoice = Invoice::factory()->create();
        $invoice->delete();

        $this->assertSoftDeleted($invoice);
    }

    #[Test]
    public function doctor_relationship(): void
    {
        $doctor = Doctor::factory()->create();
        $invoice = Invoice::factory()->create(['doctor_id' => $doctor->id]);

        $this->assertInstanceOf(Doctor::class, $invoice->doctor);
        $this->assertSame($doctor->id, $invoice->doctor->id);
    }

    #[Test]
    public function patient_relationship(): void
    {
        $patient = Patient::factory()->create();
        $invoice = Invoice::factory()->create(['patient_id' => $patient->id]);

        $this->assertInstanceOf(Patient::class, $invoice->patient);
        $this->assertSame($patient->id, $invoice->patient->id);
    }

    #[Test]
    public function appointment_relationship(): void
    {
        $appointment = Appointment::factory()->create();
        $invoice = Invoice::factory()->create(['appointment_id' => $appointment->id]);

        $this->assertInstanceOf(Appointment::class, $invoice->appointment);
        $this->assertSame($appointment->id, $invoice->appointment->id);
    }

    #[Test]
    public function items_relationship(): void
    {
        $invoice = Invoice::factory()->create();
        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_name' => 'Test',
            'quantity' => 1,
            'unit_price' => 1000,
        ]);

        $this->assertCount(1, $invoice->items);
    }

    #[Test]
    public function payment_transactions_relationship(): void
    {
        $invoice = Invoice::factory()->create();
        \App\Models\PaymentTransaction::create([
            'invoice_id' => $invoice->id,
            'transaction_reference' => 'TEST-REF',
            'payment_method' => 'paypal',
            'amount' => 10000,
            'currency' => 'ILS',
            'status' => 'completed',
        ]);

        $this->assertCount(1, $invoice->paymentTransactions);
    }
}
