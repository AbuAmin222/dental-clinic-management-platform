<?php

declare(strict_types=1);

namespace App\Services\PaymentService;

use App\Enums\SalaryPaymentStatus;
use App\Exceptions\BusinessRuleViolationException;
use App\Models\Financial;
use App\Models\SalaryPayment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Payroll ledger operations. Confirmed ownership: the ENTIRE lifecycle below (record,
 * approve, hold, cancel, reject, markAsPaid) belongs to the Financial role exclusively —
 * Admin's only payroll-related responsibility in this system is setting the policy-level
 * `users.base_salary` rate, handled separately by Admin\UserSalaryController.
 *
 * Every write is attributed to the acting Financial officer via `processed_by_financial_id`.
 */
class SalaryPaymentService
{
    /**
     * Records a new payroll entry for any staff member. `baseAmount` defaults to the
     * recipient's current `users.base_salary` when not explicitly overridden, but Financial
     * may deviate per period via $deductionAmount/$bonusAmount (or a fully custom
     * $baseAmount override) — the recorded entry is always what was actually decided for
     * that period, independent of any later change to the user's base rate.
     *
     * @throws BusinessRuleViolationException If a payment for this exact pay period already exists for the user.
     */
    public function record(
        User $recipient,
        Financial $processedBy,
        string $periodStart,
        string $periodEnd,
        ?float $baseAmount = null,
        float $deductionAmount = 0.0,
        float $bonusAmount = 0.0,
        ?string $notes = null,
    ): SalaryPayment {
        return DB::transaction(function () use ($recipient, $processedBy, $periodStart, $periodEnd, $baseAmount, $deductionAmount, $bonusAmount, $notes) {
            $duplicate = SalaryPayment::where('user_id', $recipient->id)
                ->where('pay_period_start', $periodStart)
                ->where('pay_period_end', $periodEnd)
                ->exists();

            if ($duplicate) {
                throw new BusinessRuleViolationException(
                    __('A salary payment for this user and pay period already exists.')
                );
            }

            $resolvedBase = $baseAmount ?? (float) ($recipient->base_salary ?? 0);

            if ($resolvedBase <= 0.0) {
                throw new BusinessRuleViolationException(
                    __('This user has no base salary configured. Ask an Admin to set one, or provide an explicit amount.')
                );
            }

            // Audit finding (numeric column review, 2026-08-11): `amount` is computed as
            // base - deduction + bonus in SalaryPayment::booted(), then stored in an
            // unsignedBigInteger column. Without this check, a deduction larger than
            // base+bonus would compute a negative net amount and fail as a raw, unfriendly
            // SQL "out of range" error at INSERT time instead of a clear business message.
            if ($deductionAmount > ($resolvedBase + $bonusAmount)) {
                throw new BusinessRuleViolationException(
                    __('The deduction amount cannot exceed the base salary plus any bonus for this period.')
                );
            }

            return SalaryPayment::create([
                'user_id'                   => $recipient->id,
                'processed_by_financial_id' => $processedBy->id,
                'base_amount'               => $resolvedBase,
                'deduction_amount'          => $deductionAmount,
                'bonus_amount'              => $bonusAmount,
                'pay_period_start'          => $periodStart,
                'pay_period_end'            => $periodEnd,
                'status'                    => SalaryPaymentStatus::Pending,
                'notes'                     => $notes,
            ]);
        });
    }

    /**
     * @throws BusinessRuleViolationException If not currently pending or held.
     */
    public function approve(SalaryPayment $payment): SalaryPayment
    {
        return $this->transitionLocked($payment, [SalaryPaymentStatus::Pending, SalaryPaymentStatus::Held], SalaryPaymentStatus::Approved);
    }

    /**
     * Places a pending/approved payment on hold (e.g. a dispute needs resolving before
     * disbursement) without rejecting it outright — distinct from reject(), which is final.
     */
    public function hold(SalaryPayment $payment, string $reason): SalaryPayment
    {
        return $this->transitionLocked($payment, [SalaryPaymentStatus::Pending, SalaryPaymentStatus::Approved], SalaryPaymentStatus::Held, $reason);
    }

    /**
     * Cancels a payment that has not yet been paid — distinct from reject(), which implies
     * the request was invalid; cancel implies it was valid but is no longer going ahead
     * (e.g. the employee left before this period's payout).
     */
    public function cancel(SalaryPayment $payment, string $reason): SalaryPayment
    {
        return $this->transitionLocked($payment, [SalaryPaymentStatus::Pending, SalaryPaymentStatus::Approved, SalaryPaymentStatus::Held], SalaryPaymentStatus::Cancelled, $reason);
    }

    public function reject(SalaryPayment $payment, string $reason): SalaryPayment
    {
        return $this->transitionLocked($payment, [SalaryPaymentStatus::Pending, SalaryPaymentStatus::Held], SalaryPaymentStatus::Rejected, $reason);
    }

    /**
     * @throws BusinessRuleViolationException If not currently approved.
     */
    public function markAsPaid(SalaryPayment $payment): SalaryPayment
    {
        return DB::transaction(function () use ($payment) {
            /** @var SalaryPayment $locked */
            $locked = SalaryPayment::where('id', $payment->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== SalaryPaymentStatus::Approved) {
                throw new BusinessRuleViolationException(
                    __('Only an approved salary payment can be marked as paid.')
                );
            }

            $locked->update(['status' => SalaryPaymentStatus::Paid, 'paid_at' => now()]);

            return $locked->refresh();
        });
    }

    /**
     * Shared locked-transition helper — every lifecycle move (except markAsPaid, which has
     * its own paid_at side effect) funnels through here so the "must be in an allowed
     * current state" check is written once, not duplicated per method.
     *
     * @param SalaryPaymentStatus[] $allowedFromStatuses
     * @throws BusinessRuleViolationException
     */
    private function transitionLocked(SalaryPayment $payment, array $allowedFromStatuses, SalaryPaymentStatus $toStatus, ?string $notes = null): SalaryPayment
    {
        return DB::transaction(function () use ($payment, $allowedFromStatuses, $toStatus, $notes) {
            /** @var SalaryPayment $locked */
            $locked = SalaryPayment::where('id', $payment->id)->lockForUpdate()->firstOrFail();

            if (! in_array($locked->status, $allowedFromStatuses, true)) {
                throw new BusinessRuleViolationException(
                    __('Cannot move salary payment #:id from :from to :to.', [
                        'id' => $locked->id,
                        'from' => $locked->status->value,
                        'to' => $toStatus->value,
                    ])
                );
            }

            $locked->update(array_filter([
                'status' => $toStatus,
                'notes'  => $notes,
            ], fn($v) => $v !== null));

            return $locked->refresh();
        });
    }
}
