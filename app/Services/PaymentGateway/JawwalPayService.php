<?php

declare(strict_types=1);

namespace App\Services\PaymentGateway;

use App\Contracts\Payment\PaymentStrategyInterface;
use App\Models\Invoice;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class JawwalPayService implements PaymentStrategyInterface
{
    public function initializePayment(Invoice $invoice, float $amount): array
    {
        $localTxId = 'JWP_' . Str::random(12);

        return DB::transaction(function () use ($invoice, $amount, $localTxId) {
            $transaction = PaymentTransaction::create([
                'invoice_id'     => $invoice->id,
                'transaction_id' => $localTxId,
                'payment_method' => 'jawwal_pay',
                'amount'         => $amount,
                'currency'       => 'ILS',
                'status'         => 'pending',
            ]);

            try {
                $redirectUrl = route('patient.payment.sandbox.gateway', [
                    'gateway' => 'jawwal_pay',
                    'amount'  => $amount,
                    'tx'      => $localTxId,
                ]);

                return [
                    'success'        => true,
                    'redirect_url'   => $redirectUrl,
                    'transaction_id' => $localTxId,
                ];
            } catch (Throwable $e) {
                $transaction->update([
                    'status'           => 'failed',
                    'gateway_response' => ['error' => $e->getMessage()],
                ]);

                throw new RuntimeException("The JawwalPay payment gateway is currently unable to process the request.", 0, $e);
            }
        });
    }
}
