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

class BankOfPalestineCardService implements PaymentStrategyInterface
{
    public function initializePayment(Invoice $invoice, float $amount): array
    {
        $localTxId = 'BOP_' . Str::random(12);

        return DB::transaction(function () use ($invoice, $amount, $localTxId) {
            $transaction = PaymentTransaction::create([
                'invoice_id'     => $invoice->id,
                'transaction_id' => $localTxId,
                'payment_method' => PaymentMethod::Visa,
                'amount'         => $amount,
                'currency'       => 'ILS',
                'status'         => PaymentTransactionStatus::Pending,
            ]);

            try {
                $redirectUrl = route('patient.payment.sandbox.gateway', [
                    'gateway' => PaymentMethod::Visa->value,
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
                    'status'           => PaymentTransactionStatus::Failed,
                    'gateway_response' => ['error' => $e->getMessage()],
                ]);

                throw new RuntimeException("An error occurred while connecting to Bank of Palestine gateway.", 0, $e);
            }
        });
    }
}
