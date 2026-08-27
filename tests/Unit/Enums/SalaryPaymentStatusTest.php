<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\SalaryPaymentStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SalaryPaymentStatusTest extends TestCase
{
    #[Test]
    public function values_method_returns_all_status_values(): void
    {
        $values = SalaryPaymentStatus::values();

        $this->assertSame(['pending', 'approved', 'held', 'paid', 'rejected', 'cancelled'], $values);
    }

    #[Test]
    public function cases_are_string_backed(): void
    {
        $this->assertSame('pending', SalaryPaymentStatus::Pending->value);
        $this->assertSame('approved', SalaryPaymentStatus::Approved->value);
        $this->assertSame('held', SalaryPaymentStatus::Held->value);
        $this->assertSame('paid', SalaryPaymentStatus::Paid->value);
        $this->assertSame('rejected', SalaryPaymentStatus::Rejected->value);
        $this->assertSame('cancelled', SalaryPaymentStatus::Cancelled->value);
    }

    #[Test]
    public function all_cases_can_be_created_from_value(): void
    {
        foreach (SalaryPaymentStatus::cases() as $status) {
            $this->assertSame($status, SalaryPaymentStatus::from($status->value));
        }
    }
}
