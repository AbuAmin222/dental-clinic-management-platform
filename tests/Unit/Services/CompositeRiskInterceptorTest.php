<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Risk;

use App\Models\Invoice;
use App\Models\Patient;
use App\Models\PaymentTransaction;
use App\Services\Risk\CompositeRiskInterceptor;
use App\Services\Risk\Rules\AmountThresholdRule;
use App\Services\Risk\Rules\TransactionVelocityRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CompositeRiskInterceptorTest extends TestCase
{
    use RefreshDatabase;

    private AmountThresholdRule $amountRule;
    private TransactionVelocityRule $velocityRule;

    protected function setUp(): void
    {
        parent::setUp();

        $this->amountRule = new AmountThresholdRule();
        $this->velocityRule = new TransactionVelocityRule();
    }

    #[Test]
    public function assess_returns_zero_score_for_low_amount_transaction(): void
    {
        $interceptor = new CompositeRiskInterceptor([
            $this->amountRule,
            $this->velocityRule,
        ]);

        $invoice = Invoice::factory()->create();
        $transaction = PaymentTransaction::create([
            'invoice_id' => $invoice->id,
            'transaction_reference' => 'REF-123',
            'payment_method' => 'paypal',
            'amount' => 1000,
            'currency' => 'ILS',
            'status' => 'completed',
        ]);

        $result = $interceptor->assess($transaction);

        $this->assertSame(0, $result->score);
        $this->assertFalse($result->requiresHold);
        $this->assertNull($result->reason);
    }

    #[Test]
    public function assess_triggers_hold_when_amount_exceeds_threshold(): void
    {
        $interceptor = new CompositeRiskInterceptor([
            $this->amountRule,
            $this->velocityRule,
        ]);

        $invoice = Invoice::factory()->create();
        $transaction = PaymentTransaction::create([
            'invoice_id' => $invoice->id,
            'transaction_reference' => 'REF-123',
            'payment_method' => 'paypal',
            'amount' => 5000000,
            'currency' => 'ILS',
            'status' => 'completed',
        ]);

        $result = $interceptor->assess($transaction);

        $this->assertGreaterThanOrEqual(70, $result->score);
        $this->assertTrue($result->requiresHold);
        $this->assertSame('risk_threshold_exceeded', $result->reason);
    }

    #[Test]
    public function assess_clamps_score_to_maximum_100(): void
    {
        $interceptor = new CompositeRiskInterceptor(
            [
                new AmountThresholdRule(1, 60),
                new TransactionVelocityRule(15, 2, 50),
            ],
            holdThreshold: 70
        );

        $invoice = Invoice::factory()->create();
        $transaction = PaymentTransaction::create([
            'invoice_id' => $invoice->id,
            'transaction_reference' => 'REF-123',
            'payment_method' => 'paypal',
            'amount' => 10000,
            'currency' => 'ILS',
            'status' => 'completed',
        ]);

        $result = $interceptor->assess($transaction);

        $this->assertLessThanOrEqual(100, $result->score);
    }

    #[Test]
    public function assess_with_empty_rules_returns_zero_score(): void
    {
        $interceptor = new CompositeRiskInterceptor([]);

        $invoice = Invoice::factory()->create();
        $transaction = PaymentTransaction::create([
            'invoice_id' => $invoice->id,
            'transaction_reference' => 'REF-123',
            'payment_method' => 'paypal',
            'amount' => 1000000,
            'currency' => 'ILS',
            'status' => 'completed',
        ]);

        $result = $interceptor->assess($transaction);

        $this->assertSame(0, $result->score);
        $this->assertFalse($result->requiresHold);
    }

    #[Test]
    public function assess_with_custom_hold_threshold(): void
    {
        $interceptor = new CompositeRiskInterceptor(
            [new AmountThresholdRule(1, 50)],
            holdThreshold: 40
        );

        $invoice = Invoice::factory()->create();
        $transaction = PaymentTransaction::create([
            'invoice_id' => $invoice->id,
            'transaction_reference' => 'REF-123',
            'payment_method' => 'paypal',
            'amount' => 10000,
            'currency' => 'ILS',
            'status' => 'completed',
        ]);

        $result = $interceptor->assess($transaction);

        $this->assertTrue($result->requiresHold);
    }
}
