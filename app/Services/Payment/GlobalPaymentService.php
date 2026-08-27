<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Contracts\Payment\PaymentStrategyInterface;
use App\Enums\PaymentTransactionStatus;
use App\Contracts\Risk\RiskInterceptorInterface;
use App\Factories\Payment\PaymentManagerFactory;
use App\Models\Invoice;
use App\Models\PaymentTransaction;
use App\Services\PaymentGateway\CurrencyConverter;
use Illuminate\Support\Facades\DB;

class GlobalPaymentService
{
    public function __construct(
        private readonly RiskInterceptorInterface $riskInterceptor,
        private readonly CurrencyConverter $currencyConverter,
    ) {}

    /**
     * @return array{success: bool, redirect_url: string, transaction_id: string}
     */
    public function initialize(Invoice $invoice, float $amount, string $method): array
    {
        /** @var PaymentStrategyInterface $strategy */
        $strategy = PaymentManagerFactory::make($method);

        return $strategy->initializePayment($invoice, $amount);
    }

    /**
     * Called once a gateway confirms a transaction succeeded (e.g. from a webhook/callback
     * handler). Runs the risk assessment and either completes the transaction normally or
     * places it on hold for financial-officer review — the invoice's paid_amount is only
     * ever touched on a genuinely 'completed' outcome, never on a held one.
     */
    public function confirmSuccessfulTransaction(PaymentTransaction $transaction): PaymentTransaction
    {
        return DB::transaction(function () use ($transaction) {
            /** @var PaymentTransaction $locked */
            $locked = PaymentTransaction::where('id', $transaction->id)->lockForUpdate()->firstOrFail();

            $assessment = $this->riskInterceptor->assess($locked);

            if ($assessment->requiresHold) {
                $locked->update([
                    'status' => PaymentTransactionStatus::HeldForReview,
                    'notes'  => "risk_score={$assessment->score}; reason={$assessment->reason}",
                ]);

                return $locked->refresh();
            }

            $locked->invoice->recordPayment($this->toIls($locked));
            $locked->update(['status' => PaymentTransactionStatus::Completed]);

            return $locked->refresh();
        });
    }

    private function toIls(PaymentTransaction $transaction): float
    {
        return match ($transaction->currency) {
            'USD'   => $this->currencyConverter->usdToIls((float) $transaction->amount),
            default => (float) $transaction->amount,
        };
    }
}
