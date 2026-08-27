<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\DayOfWeek;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DayOfWeekTest extends TestCase
{
    #[Test]
    public function values_method_returns_all_days(): void
    {
        $values = DayOfWeek::values();

        $this->assertSame(
            ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            $values
        );
    }

    #[Test]
    public function cases_are_string_backed(): void
    {
        $this->assertSame('saturday', DayOfWeek::Saturday->value);
        $this->assertSame('sunday', DayOfWeek::Sunday->value);
        $this->assertSame('monday', DayOfWeek::Monday->value);
        $this->assertSame('tuesday', DayOfWeek::Tuesday->value);
        $this->assertSame('wednesday', DayOfWeek::Wednesday->value);
        $this->assertSame('thursday', DayOfWeek::Thursday->value);
        $this->assertSame('friday', DayOfWeek::Friday->value);
    }

    #[Test]
    public function all_cases_can_be_created_from_value(): void
    {
        foreach (DayOfWeek::cases() as $day) {
            $this->assertSame($day, DayOfWeek::from($day->value));
        }
    }
}
