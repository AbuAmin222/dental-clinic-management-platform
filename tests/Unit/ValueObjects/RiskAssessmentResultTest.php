<?php

declare(strict_types=1);

namespace Tests\Unit\ValueObjects;

use App\ValueObjects\RiskAssessmentResult;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RiskAssessmentResultTest extends TestCase
{
    #[Test]
    public function constructor_sets_all_properties(): void
    {
        $result = new RiskAssessmentResult(
            score: 85,
            requiresHold: true,
            reason: 'risk_threshold_exceeded'
        );

        $this->assertSame(85, $result->score);
        $this->assertTrue($result->requiresHold);
        $this->assertSame('risk_threshold_exceeded', $result->reason);
    }

    #[Test]
    public function constructor_allows_null_reason(): void
    {
        $result = new RiskAssessmentResult(
            score: 30,
            requiresHold: false,
            reason: null
        );

        $this->assertSame(30, $result->score);
        $this->assertFalse($result->requiresHold);
        $this->assertNull($result->reason);
    }

    #[Test]
    public function constructor_defaults_reason_to_null(): void
    {
        $result = new RiskAssessmentResult(
            score: 20,
            requiresHold: false
        );

        $this->assertNull($result->reason);
    }

    #[Test]
    public function readonly_class_prevents_modification(): void
    {
        $reflection = new \ReflectionClass(RiskAssessmentResult::class);

        $this->assertTrue($reflection->isReadOnly());
    }

    #[Test]
    public function score_can_be_zero(): void
    {
        $result = new RiskAssessmentResult(
            score: 0,
            requiresHold: false
        );

        $this->assertSame(0, $result->score);
        $this->assertFalse($result->requiresHold);
    }

    #[Test]
    public function score_can_be_maximum_100(): void
    {
        $result = new RiskAssessmentResult(
            score: 100,
            requiresHold: true,
            reason: 'maximum_risk'
        );

        $this->assertSame(100, $result->score);
        $this->assertTrue($result->requiresHold);
    }
}
