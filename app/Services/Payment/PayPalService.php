<?php

namespace App\Services\Payment;

use App\Models\Invoice;
use App\Models\PaymentTransaction;
use Illuminate\Support\Str;
use Exception;

class PayPalService implements PaymentStrategy
{
    public function __construct(protected CurrencyConverter $converter = new CurrencyConverter()) {}

    public function initializePayment(Invoice $invoice, float $amount): array
    {
        $localTxId = 'PAYPAL_' . Str::random(12);

        // $amount arrives in ILS like every other gateway here - PayPal needs USD.
        $amountUsd = $this->converter->ilsToUsd($amount);

        $transaction = PaymentTransaction::create([
            'invoice_id'     => $invoice->id,
            'transaction_id' => $localTxId,
            'payment_method' => 'paypal',
            'amount'         => $amountUsd,
            'currency'       => 'USD',
            'status'         => 'pending',
        ]);

        try {
            $redirectUrl = route('patient.payment.sandbox.gateway', [
                'gateway' => 'paypal',
                'amount'  => $amountUsd,
                'tx'      => $localTxId
            ]);

            return [
                'success'        => true,
                'redirect_url'   => $redirectUrl,
                'transaction_id' => $localTxId,
            ];
        } catch (Exception $e) {
            $transaction->update(['status' => 'failed', 'gateway_response' => ['error' => $e->getMessage()]]);
            throw new Exception("حدث خطأ أثناء الاتصال ببوابة PayPal العالمية.");
        }
    }
}
