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
        $this->invoice = Invoice::factory()->create();
    }

    #[Test]
    public function evaluate_returns_points_for_amount_above_threshold(): void
    {
        $rule = new AmountThresholdRule(thresholdMinorUnits: 500_000, points: 40);

        $transaction = PaymentTransaction::create([
            'invoice_id' => $this->invoice->id,
            'transaction_reference' => 'REF-123',
            'payment_method' => 'paypal',
            'amount' => 600000,
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
            'amount' => 400000,
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
            'amount' => 500000,
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
            'amount' => 400000,
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
            'amount' => 150000,
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
            'amount' => null,
            'currency' => 'ILS',
            'status' => 'completed',
        ]);

        $this->assertSame(0, $rule->evaluate($transaction));
    }
}
