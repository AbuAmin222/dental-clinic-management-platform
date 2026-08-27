<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\SalaryPaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A single payroll disbursement event for ANY staff member.
 * Confirmed decision: the entire
 * lifecycle (record/approve/reject/hold/cancel, plus per-period deduction/bonus adjustments)
 * is owned exclusively by the Financial role — never Admin, who only sets the policy-level
 * base rate on `users.base_salary`.
 *
 * `amount` (net) is never set directly — always derived from base/deduction/bonus in
 * booted(), so the three components and the total can never drift out of sync.
 */
class SalaryPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'processed_by_financial_id',
        'base_amount',
        'deduction_amount',
        'bonus_amount',
        'pay_period_start',
        'pay_period_end',
        'status',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'base_amount'       => MoneyCast::class,
        'deduction_amount'  => MoneyCast::class,
        'bonus_amount'      => MoneyCast::class,
        'amount'            => MoneyCast::class,
        'pay_period_start'  => 'date',
        'pay_period_end'    => 'date',
        'paid_at'           => 'datetime',
        'status'            => SalaryPaymentStatus::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(Financial::class, 'processed_by_financial_id');
    }

    protected static function booted(): void
    {
        static::saving(function (self $payment): void {
            $base      = (int) ($payment->getRawOriginal('base_amount') ?? 0);
            $deduction = (int) ($payment->getRawOriginal('deduction_amount') ?? 0);
            $bonus     = (int) ($payment->getRawOriginal('bonus_amount') ?? 0);

            // التعيين المباشر في مصفوفة attributes لضمان تضمين الحقل في استعلام INSERT
            $payment->attributes['amount'] = $base - $deduction + $bonus;

            // $payment->amount = $payment->base_amount - $payment->deduction_amount + $payment->bonus_amount;
        });
    }
}
