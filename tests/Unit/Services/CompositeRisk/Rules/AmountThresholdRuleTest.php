<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Risk;

use App\Models\Invoice;
use App\Models\PaymentTransaction;
use App\Services\Risk\Rules\AmountThresholdRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AmountThresholdRuleTest extends TestCase
{
    use RefreshDatabase;

    private Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();
        $doctor = \App\Models\Doctor::factory()->create();
        $patient = \App\Models\Patient::factory()->create();
        $this->invoice = Invoice::create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'sub_total' => 100.0,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 100.0,
            'paid_amount' => 0.0,
            'due_amount' => 100.0,
            'status' => \App\Enums\InvoiceStatus::Pending,
            'due_date' => '2025-12-31',
        ]);
    }

    #[Test]
    public function evaluate_returns_points_for_amount_above_threshold(): void
    {
        $rule = new AmountThresholdRule(thresholdMinorUnits: 500_000, points: 40);

        $transaction = PaymentTransaction::create([
            'invoice_id' => $this->invoice->id,
            'transaction_reference' => 'REF-123',
            'payment_method' => 'paypal',
            'amount' => 6000.0,
            'currency' => 'ILS',
            'status' => 'completed',
        ]);

        $this->assertSame(40, $rule->evaluate($transaction));
    }

    #[Test]
    public function evaluate_returns_zero_for_amount_below_threshold(): void
    {
        $rule = new AmountThresholdRule(thresholdMinorUnits: 500_000, points: 40);

        $transaction = PaymentTransaction::create([
            'invoice_id' => $this->invoice->id,
            'transaction_reference' => 'REF-123',
            'payment_method' => 'paypal',
            'amount' => 400.0,
            'currency' => 'ILS',
            'status' => 'completed',
        ]);

        $this->assertSame(0, $rule->evaluate($transaction));
    }

    #[Test]
    public function evaluate_returns_points_for_amount_equal_to_threshold(): void
    {
        $rule = new AmountThresholdRule(thresholdMinorUnits: 500_000, points: 40);

        $transaction = PaymentTransaction::create([
            'invoice_id' => $this->invoice->id,
            'transaction_reference' => 'REF-123',
            'payment_method' => 'paypal',
            'amount' => 5000.0,
            'currency' => 'ILS',
            'status' => 'completed',
        ]);

        $this->assertSame(40, $rule->evaluate($transaction));
    }

    #[Test]
    public function evaluate_uses_config_defaults_when_no_arguments_provided(): void
    {
        $rule = new AmountThresholdRule();

        $transaction = PaymentTransaction::create([
            'invoice_id' => $this->invoice->id,
            'transaction_reference' => 'REF-123',
            'payment_method' => 'paypal',
            'amount' => 400.0,
            'currency' => 'ILS',
            'status' => 'completed',
        ]);

        $this->assertSame(0, $rule->evaluate($transaction));
    }

    #[Test]
    public function evaluate_uses_custom_threshold_and_points(): void
    {
        $rule = new AmountThresholdRule(thresholdMinorUnits: 100_000, points: 25);

        $transaction = PaymentTransaction::create([
            'invoice_id' => $this->invoice->id,
            'transaction_reference' => 'REF-123',
            'payment_method' => 'paypal',
            'amount' => 1500.0,
            'currency' => 'ILS',
            'status' => 'completed',
        ]);

        $this->assertSame(25, $rule->evaluate($transaction));
    }

    #[Test]
    public function evaluate_returns_zero_when_config_returns_null_amount(): void
    {
        $rule = new AmountThresholdRule(thresholdMinorUnits: 500_000, points: 40);

        $transaction = PaymentTransaction::create([
            'invoice_id' => $this->invoice->id,
            'transaction_reference' => 'REF-123',
            'payment_method' => 'paypal',
            'amount' => 0.0,
            'currency' => 'ILS',
            'status' => 'completed',
        ]);

        $this->assertSame(0, $rule->evaluate($transaction));

        $this->assertSame(0, $rule->evaluate($transaction));
    }
}
