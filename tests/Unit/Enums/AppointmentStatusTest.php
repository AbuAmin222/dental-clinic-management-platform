<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\AppointmentStatus;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AppointmentStatusTest extends TestCase
{
    #[Test]
    public function values_method_returns_all_status_values(): void
    {
        $values = AppointmentStatus::values();

        $this->assertSame(['pending', 'scheduled', 'confirmed', 'completed', 'cancelled', 'stopped'], $values);
    }

    #[Test]
    public function cases_are_string_backed(): void
    {
        $this->assertSame('pending', AppointmentStatus::Pending->value);
        $this->assertSame('scheduled', AppointmentStatus::Scheduled->value);
        $this->assertSame('confirmed', AppointmentStatus::Confirmed->value);
        $this->assertSame('completed', AppointmentStatus::Completed->value);
        $this->assertSame('cancelled', AppointmentStatus::Cancelled->value);
        $this->assertSame('stopped', AppointmentStatus::Stopped->value);
    }

    #[Test]
    public function all_cases_can_be_created_from_value(): void
    {
        foreach (AppointmentStatus::cases() as $status) {
            $this->assertSame($status, AppointmentStatus::from($status->value));
        }
    }

    #[Test]
    public function values_are_unique(): void
    {
        $values = AppointmentStatus::values();

        $this->assertSame(count($values), count(array_unique($values)));
    }
}
