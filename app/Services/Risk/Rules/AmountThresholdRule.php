<?php

declare(strict_types=1);

namespace App\Services\Risk\Rules;

use App\Contracts\Risk\RiskRuleInterface;
use App\Models\PaymentTransaction;

/**
 * Flags transactions above a fixed monetary threshold. $thresholdMinorUnits is expressed
 * in the same integer minor-unit (agorot) representation as PaymentTransaction::amount's
 * raw DB value — the Model's MoneyCast is bypassed here deliberately since this rule reads
 * getRawOriginal() for a cheap comparison without triggering the cast round-trip.
 *
 * مركزية المتغيرات (2026-08-11): القيم الفعلية في config/clinic.php وليست هنا — تعديل حد
 * المخاطرة مستقبلاً يعني تعديل رقم واحد في ملف الإعدادات فقط، دون لمس هذا الكلاس.
 */
final class AmountThresholdRule implements RiskRuleInterface
{
    private readonly int $thresholdMinorUnits;
    private readonly int $points;

    public function __construct(?int $thresholdMinorUnits = null, ?int $points = null)
    {
        $this->thresholdMinorUnits = $thresholdMinorUnits ?? (int) config('clinic.risk.amount_threshold_minor_units', 500_000);
        $this->points = $points ?? (int) config('clinic.risk.amount_threshold_points', 40);
    }

    public function evaluate(PaymentTransaction $transaction): int
    {
        $amountMinor = (int) $transaction->getRawOriginal('amount');

        return $amountMinor >= $this->thresholdMinorUnits ? $this->points : 0;
    }
}
