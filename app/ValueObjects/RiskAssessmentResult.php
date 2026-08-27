<?php

declare(strict_types=1);

namespace App\ValueObjects;

final readonly class RiskAssessmentResult
{
    public function __construct(
        public int $score,
        public bool $requiresHold,
        public ?string $reason = null,
    ) {}
}
