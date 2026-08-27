<?php

declare(strict_types=1);

namespace App\Services\Risk\Rules;

use App\Contracts\Risk\RiskRuleInterface;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;

/**
 * Flags a patient attempting many payment transactions in a short window — a common
 * card-testing / fraud signal. Counts sibling PaymentTransaction rows across the same
 * patient's invoices within the lookback window.
 *
 * مركزية المتغيرات (2026-08-11): القيم من config/clinic.php.
 */
final class TransactionVelocityRule implements RiskRuleInterface
{
    private readonly int $lookbackMinutes;
    private readonly int $attemptThreshold;
    private readonly int $points;

    public function __construct(?int $lookbackMinutes = null, ?int $attemptThreshold = null, ?int $points = null)
    {
        $this->lookbackMinutes = $lookbackMinutes ?? (int) config('clinic.risk.velocity_lookback_minutes', 15);
        $this->attemptThreshold = $attemptThreshold ?? (int) config('clinic.risk.velocity_attempt_threshold', 4);
        $this->points = $points ?? (int) config('clinic.risk.velocity_points', 35);
    }

    public function evaluate(PaymentTransaction $transaction): int
    {
        $patientId = $transaction->invoice?->patient_id;

        if ($patientId === null) {
            return 0;
        }

        $recentAttempts = DB::table('payment_transactions')
            ->join('invoices', 'invoices.id', '=', 'payment_transactions.invoice_id')
            ->where('invoices.patient_id', $patientId)
            ->where('payment_transactions.created_at', '>=', now()->subMinutes($this->lookbackMinutes))
            ->count();

        return $recentAttempts >= $this->attemptThreshold ? $this->points : 0;
    }
}
