<?php

namespace App\Services\Payment;

use App\Models\Invoice;
use App\Models\PaymentTransaction;
use Illuminate\Support\Str;
use Exception;

class BankOfPalestineCardService implements PaymentStrategy
{
    public function initializePayment(Invoice $invoice, float $amount): array
    {
        $localTxId = 'BOP_' . Str::random(12);

        $transaction = PaymentTransaction::create([
            'invoice_id'     => $invoice->id,
            'transaction_id' => $localTxId,
            'payment_method' => 'visa',
            'amount'         => $amount,
            'currency'       => 'ILS',
            'status'         => 'pending',
        ]);

        try {
            // توجيه المريض إلى صفحة محاكاة بنك فلسطين المدمجة داخل مشروعك بأمان
            $redirectUrl = route('patient.payment.sandbox.gateway', [
                'gateway' => 'bop',
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
            throw new Exception("حدث خطأ أثناء الاتصال ببوابة بنك فلسطين الالكترونية.");
        }
    }
}
