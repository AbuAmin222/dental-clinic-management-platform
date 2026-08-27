<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\InvoiceStatus;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class InvoiceStatusTest extends TestCase
{
    #[Test]
    public function values_method_returns_all_status_values(): void
    {
        $values = InvoiceStatus::values();

        $this->assertSame(['draft', 'pending', 'partially_paid', 'paid', 'cancelled', 'refunded'], $values);
    }

    #[Test]
    public function cases_are_string_backed(): void
    {
        $this->assertSame('draft', InvoiceStatus::Draft->value);
        $this->assertSame('pending', InvoiceStatus::Pending->value);
        $this->assertSame('partially_paid', InvoiceStatus::PartiallyPaid->value);
        $this->assertSame('paid', InvoiceStatus::Paid->value);
        $this->assertSame('cancelled', InvoiceStatus::Cancelled->value);
        $this->assertSame('refunded', InvoiceStatus::Refunded->value);
    }

    #[Test]
    public function all_cases_can_be_created_from_value(): void
    {
        foreach (InvoiceStatus::cases() as $status) {
            $this->assertSame($status, InvoiceStatus::from($status->value));
        }
    }

    #[Test]
    public function try_from_returns_null_for_invalid_value(): void
    {
        $this->assertNull(InvoiceStatus::tryFrom('invalid'));
    }
}
