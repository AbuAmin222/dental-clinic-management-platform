<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Enums\PaymentMethod;
use App\Enums\PaymentTransactionStatus;
use App\Exceptions\BusinessRuleViolationException;
use App\Models\Invoice;
use App\Models\LocalPaymentMethod;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Implements the manual proof-of-payment flow confirmed in ADR-006 (DECISIONS_LOG.md),
 * superseding the old gateway-callback model for the three local wallet-style gateways
 * (Bank of Palestine wallet / JawwalPay / PalPay as *local* transfer methods — not to be
 * confused with the automated card-based BankOfPalestineCardService, which is unaffected).
 */
class LocalPaymentService
{
    /**
     * Patient step: submit proof of a manual transfer. Creates a PaymentTransaction in
     * `pending` status awaiting financial-officer review — never touches the invoice's
     * paid_amount until reviewSubmission() approves it.
     */
    public function submitProof(Invoice $invoice, LocalPaymentMethod $method, float $amount, string $proofImagePath): PaymentTransaction
    {
        return DB::transaction(function () use ($invoice, $method, $amount, $proofImagePath) {
            return PaymentTransaction::create([
                'invoice_id'               => $invoice->id,
                'local_payment_method_id'  => $method->id,
                'transaction_reference'    => 'LOCAL_' . Str::upper(Str::random(12)),
                'payment_method'           => PaymentMethod::LocalTransfer,
                'amount'                   => $amount,
                'currency'                 => 'ILS',
                'status'                   => PaymentTransactionStatus::Pending,
                'proof_image_path'         => $proofImagePath,
            ]);
        });
    }

    /**
     * Financial-officer step: approve a pending local-transfer submission. Delegates the
     * actual balance/state update to Invoice::recordPayment() (via InvoiceService would be
     * preferable in a Controller; kept as a direct Model call here since the pessimistic
     * lock already lives inside recordPayment() itself — see PaymentService\InvoiceService
     * for the identical lock pattern this mirrors).
     *
     * @throws BusinessRuleViolationException If the transaction is not in a reviewable state.
     */
    public function approve(PaymentTransaction $transaction, User $reviewer, ?string $notes = null): PaymentTransaction
    {
        return DB::transaction(function () use ($transaction, $reviewer, $notes) {
            /** @var PaymentTransaction $locked */
            $locked = PaymentTransaction::where('id', $transaction->id)->lockForUpdate()->firstOrFail();

            if (! in_array($locked->status, [PaymentTransactionStatus::Pending, PaymentTransactionStatus::HeldForReview], true)) {
                throw new BusinessRuleViolationException(
                    __('This payment transaction has already been reviewed.')
                );
            }

            $locked->invoice->recordPayment((float) $locked->amount);

            $locked->update([
                'status' => PaymentTransactionStatus::Completed,
                'notes'  => $notes,
            ]);

            return $locked->refresh();
        });
    }

    /**
     * Financial-officer step: reject a pending local-transfer submission. The invoice is
     * never touched — its paid_amount/status remain exactly as they were before submission.
     */
    public function reject(PaymentTransaction $transaction, User $reviewer, string $reason): PaymentTransaction
    {
        return DB::transaction(function () use ($transaction, $reason) {
            /** @var PaymentTransaction $locked */
            $locked = PaymentTransaction::where('id', $transaction->id)->lockForUpdate()->firstOrFail();

            if (! in_array($locked->status, [PaymentTransactionStatus::Pending, PaymentTransactionStatus::HeldForReview], true)) {
                throw new BusinessRuleViolationException(
                    __('This payment transaction has already been reviewed.')
                );
            }

            $locked->update([
                'status' => PaymentTransactionStatus::Rejected,
                'notes'  => $reason,
            ]);

            return $locked->refresh();
        });
    }
}
