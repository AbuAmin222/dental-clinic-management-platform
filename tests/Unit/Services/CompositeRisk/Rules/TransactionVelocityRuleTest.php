<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Risk;

use App\Models\Invoice;
use App\Models\Patient;
use App\Models\PaymentTransaction;
use App\Services\Risk\Rules\TransactionVelocityRule;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TransactionVelocityRuleTest extends TestCase
{
    use RefreshDatabase;

    private Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->patient = Patient::factory()->create();
    }

    private function createTransactionForPatient(?int $minutesAgo = null): PaymentTransaction
    {
        $invoice = Invoice::factory()->create(['patient_id' => $this->patient->id]);
        $time = $minutesAgo !== null ? Carbon::now()->subMinutes($minutesAgo) : Carbon::now();

        return PaymentTransaction::create([
            'invoice_id' => $invoice->id,
            'transaction_reference' => 'REF-' . uniqid(),
            'payment_method' => 'paypal',
            'amount' => 10000,
            'currency' => 'ILS',
            'status' => 'completed',
        ]);
    }

    #[Test]
    public function evaluate_returns_zero_for_no_recent_transactions(): void
    {
        $rule = new TransactionVelocityRule(lookbackMinutes: 15, attemptThreshold: 4, points: 35);

        $transaction = $this->createTransactionForPatient();

        $this->assertSame(0, $rule->evaluate($transaction));
    }

    #[Test]
    public function evaluate_returns_points_when_attempt_threshold_reached(): void
    {
        $rule = new TransactionVelocityRule(lookbackMinutes: 15, attemptThreshold: 4, points: 35);

        $this->createTransactionForPatient(1);
        $this->createTransactionForPatient(2);
        $this->createTransactionForPatient(3);
        $transaction = $this->createTransactionForPatient(5);

        $this->assertSame(35, $rule->evaluate($transaction));
    }

    #[Test]
    public function evaluate_returns_zero_when_below_attempt_threshold(): void
    {
        $rule = new TransactionVelocityRule(lookbackMinutes: 15, attemptThreshold: 4, points: 35);

        $this->createTransactionForPatient(1);
        $this->createTransactionForPatient(2);
        $transaction = $this->createTransactionForPatient(5);

        $this->assertSame(0, $rule->evaluate($transaction));
    }

    #[Test]
    public function evaluate_returns_zero_when_invoice_has_no_patient(): void
    {
        $rule = new TransactionVelocityRule(lookbackMinutes: 15, attemptThreshold: 4, points: 35);

        $invoice = Invoice::factory()->create(['patient_id' => null]);
        $transaction = PaymentTransaction::create([
            'invoice_id' => $invoice->id,
            'transaction_reference' => 'REF-123',
            'payment_method' => 'paypal',
            'amount' => 10000,
            'currency' => 'ILS',
            'status' => 'completed',
        ]);

        $this->assertSame(0, $rule->evaluate($transaction));
    }

    #[Test]
    public function evaluate_only_counts_transactions_within_lookback_window(): void
    {
        $rule = new TransactionVelocityRule(lookbackMinutes: 15, attemptThreshold: 3, points: 35);

        $this->createTransactionForPatient(20);
        $this->createTransactionForPatient(20);
        $transaction = $this->createTransactionForPatient(2);

        $this->assertSame(35, $rule->evaluate($transaction));
    }

    #[Test]
    public function evaluate_uses_config_defaults(): void
    {
        $rule = new TransactionVelocityRule();

        $transaction = $this->createTransactionForPatient();

        $this->assertSame(0, $rule->evaluate($transaction));
    }
}
