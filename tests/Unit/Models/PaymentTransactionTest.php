<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentTransactionStatus;
use App\Models\Invoice;
use App\Models\LocalPaymentMethod;
use App\Models\PaymentTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentTransactionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    #[Test]
    public function casts_amount_with_money_cast(): void
    {
        $invoice = Invoice::factory()->create();
        $transaction = PaymentTransaction::create([
            'invoice_id' => $invoice->id,
            'transaction_reference' => 'REF-123',
            'payment_method' => 'paypal',
            'amount' => 50000,
            'currency' => 'ILS',
            'status' => 'completed',
        ]);

        $this->assertSame(500.00, $transaction->amount);
    }

    #[Test]
    public function casts_gateway_response_as_array(): void
    {
        $invoice = Invoice::factory()->create();
        $transaction = PaymentTransaction::create([
            'invoice_id' => $invoice->id,
            'transaction_reference' => 'REF-123',
            'payment_method' => 'paypal',
            'amount' => 50000,
            'currency' => 'ILS',
            'status' => 'completed',
            'gateway_response' => ['gateway' => 'paypal', 'result' => 'approved'],
        ]);

        $this->assertIsArray($transaction->gateway_response);
        $this->assertSame('approved', $transaction->gateway_response['result']);
    }

    #[Test]
    public function casts_status_as_enum(): void
    {
        $invoice = Invoice::factory()->create();
        $transaction = PaymentTransaction::create([
            'invoice_id' => $invoice->id,
            'transaction_reference' => 'REF-123',
            'payment_method' => 'paypal',
            'amount' => 50000,
            'currency' => 'ILS',
            'status' => PaymentTransactionStatus::Completed,
        ]);

        $this->assertEquals(PaymentTransactionStatus::Completed, $transaction->status);
    }

    #[Test]
    public function casts_payment_method_as_enum(): void
    {
        $invoice = Invoice::factory()->create();
        $transaction = PaymentTransaction::create([
            'invoice_id' => $invoice->id,
            'transaction_reference' => 'REF-123',
            'payment_method' => PaymentMethod::PayPal,
            'amount' => 50000,
            'currency' => 'ILS',
            'status' => 'completed',
        ]);

        $this->assertEquals(PaymentMethod::PayPal, $transaction->payment_method);
    }

    #[Test]
    public function invoice_relationship(): void
    {
        $invoice = Invoice::factory()->create();
        $transaction = PaymentTransaction::create([
            'invoice_id' => $invoice->id,
            'transaction_reference' => 'REF-123',
            'payment_method' => 'paypal',
            'amount' => 50000,
            'currency' => 'ILS',
            'status' => 'completed',
        ]);

        $this->assertInstanceOf(Invoice::class, $transaction->invoice);
    }

    #[Test]
    public function local_payment_method_relationship(): void
    {
        $localMethod = LocalPaymentMethod::factory()->create();
        $invoice = Invoice::factory()->create();

        $transaction = PaymentTransaction::create([
            'invoice_id' => $invoice->id,
            'local_payment_method_id' => $localMethod->id,
            'transaction_reference' => 'LOCAL-12345',
            'payment_method' => PaymentMethod::LocalTransfer,
            'amount' => 50000,
            'currency' => 'ILS',
            'status' => 'held_for_review',
        ]);

        $this->assertInstanceOf(LocalPaymentMethod::class, $transaction->localPaymentMethod);
    }

    #[Test]
    public function fillable_attributes(): void
    {
        $fillable = (new PaymentTransaction())->getFillable();

        $this->assertContains('invoice_id', $fillable);
        $this->assertContains('amount', $fillable);
        $this->assertContains('status', $fillable);
        $this->assertContains('payment_method', $fillable);
    }
}
