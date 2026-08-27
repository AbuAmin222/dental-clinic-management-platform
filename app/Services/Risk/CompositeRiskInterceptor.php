<?php

declare(strict_types=1);

namespace App\Services\Risk;

use App\Contracts\Risk\RiskInterceptorInterface;
use App\Contracts\Risk\RiskRuleInterface;
use App\Models\PaymentTransaction;
use App\ValueObjects\RiskAssessmentResult;

final class CompositeRiskInterceptor implements RiskInterceptorInterface
{
    /**
     * @param RiskRuleInterface[] $rules
     */
    public function __construct(
        private readonly array $rules,
        private readonly int $holdThreshold = 70, // fallback only — AppServiceProvider passes config('clinic.risk.hold_threshold') explicitly
    ) {}

    public function assess(PaymentTransaction $transaction): RiskAssessmentResult
    {
        $score = array_sum(array_map(
            static fn(RiskRuleInterface $rule): int => $rule->evaluate($transaction),
            $this->rules,
        ));

        $score = min($score, 100);

        return new RiskAssessmentResult(
            score: $score,
            requiresHold: $score >= $this->holdThreshold,
            reason: $score >= $this->holdThreshold ? 'risk_threshold_exceeded' : null,
        );
    }
}
