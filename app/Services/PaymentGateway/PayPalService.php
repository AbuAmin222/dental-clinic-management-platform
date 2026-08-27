<?php

declare(strict_types=1);

namespace App\Services\PaymentGateway;

use App\Contracts\Payment\PaymentStrategyInterface;
use App\Enums\PaymentMethod;
use App\Enums\PaymentTransactionStatus;
use App\Models\Invoice;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class PayPalService implements PaymentStrategyInterface
{
    public function __construct(
        protected CurrencyConverter $converter
    ) {}

    public function initializePayment(Invoice $invoice, float $amount): array
    {
        $localTxId = 'PAYPAL_' . Str::random(12);
        $amountUsd = $this->converter->ilsToUsd($amount);

        return DB::transaction(function () use ($invoice, $amountUsd, $localTxId) {
            $transaction = PaymentTransaction::create([
                'invoice_id'     => $invoice->id,
                'transaction_id' => $localTxId,
                'payment_method' => PaymentMethod::PayPal,
                'amount'         => $amountUsd,
                'currency'       => 'USD',
                'status'         => PaymentTransactionStatus::Pending,
            ]);

            try {
                $redirectUrl = route('patient.payment.sandbox.gateway', [
                    'gateway' => PaymentMethod::PayPal->value,
                    'amount'  => $amountUsd,
                    'tx'      => $localTxId,
                ]);

                return [
                    'success'        => true,
                    'redirect_url'   => $redirectUrl,
                    'transaction_id' => $localTxId,
                ];
            } catch (Throwable $e) {
                $transaction->update([
                    'status'           => PaymentTransactionStatus::Failed,
                    'gateway_response' => ['error' => $e->getMessage()],
                ]);

                throw new RuntimeException("An error occurred while connecting to the PayPal global gateway.", 0, $e);
            }
        });
    }
}
