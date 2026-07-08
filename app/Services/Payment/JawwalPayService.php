<?php

namespace App\Services\Payment;

use App\Models\Invoice;
use App\Models\PaymentTransaction;
use Illuminate\Support\Str;
use Exception;

class JawwalPayService implements PaymentStrategy
{
    public function initializePayment(Invoice $invoice, float $amount): array
    {
        $localTxId = 'JWP_' . Str::random(12);

        $transaction = PaymentTransaction::create([
            'invoice_id'     => $invoice->id,
            'transaction_id' => $localTxId,
            'payment_method' => 'jawwal_pay',
            'amount'         => $amount,
            'currency'       => 'ILS',
            'status'         => 'pending',
        ]);

        try {
            // التوجيه لمحاكي محفظة جوال باي المحلي لمنع انهيار الـ DNS
            $redirectUrl = route('patient.payment.sandbox.gateway', [
                'gateway' => 'jawwal_pay',
                'amount'  => $amount,
                'tx'      => $localTxId
            ]);

            return [
                'success'        => true,
                'redirect_url'   => $redirectUrl,
                'transaction_id' => $localTxId,
            ];
        } catch (Exception $e) {
            $transaction->update(['status' => 'failed', 'gateway_response' => ['error' => $e->getMessage()]]);
            throw new Exception("بوابة جوال باي غير قادرة على معالجة الطلب حالياً.");
        }
    }
}
