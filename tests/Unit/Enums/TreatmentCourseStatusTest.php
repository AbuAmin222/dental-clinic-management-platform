<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\TreatmentCourseStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TreatmentCourseStatusTest extends TestCase
{
    #[Test]
    public function values_method_returns_all_status_values(): void
    {
        $values = TreatmentCourseStatus::values();

        $this->assertSame(['ongoing', 'completed', 'cancelled'], $values);
    }

    #[Test]
    public function cases_are_string_backed(): void
    {
        $this->assertSame('ongoing', TreatmentCourseStatus::Ongoing->value);
        $this->assertSame('completed', TreatmentCourseStatus::Completed->value);
        $this->assertSame('cancelled', TreatmentCourseStatus::Cancelled->value);
    }

    #[Test]
    public function all_cases_can_be_created_from_value(): void
    {
        foreach (TreatmentCourseStatus::cases() as $status) {
            $this->assertSame($status, TreatmentCourseStatus::from($status->value));
        }
    }
}
