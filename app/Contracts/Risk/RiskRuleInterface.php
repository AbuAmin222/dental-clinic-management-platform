<?php

declare(strict_types=1);

namespace App\Contracts\Risk;

use App\Models\PaymentTransaction;

/**
 * A single, independently-testable risk-scoring rule. Adding a new fraud signal is a new
 * class implementing this interface plus one line in the Service Provider bindings array —
 * CompositeRiskInterceptor itself never changes (OCP).
 */
interface RiskRuleInterface
{
    /**
     * @return int Points to add toward the composite risk score (0-100 scale expected per rule,
     *              though the composite clamps the total regardless).
     */
    public function evaluate(PaymentTransaction $transaction): int;
}
