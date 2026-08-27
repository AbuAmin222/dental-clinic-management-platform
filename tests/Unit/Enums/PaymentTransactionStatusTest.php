<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\PaymentTransactionStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PaymentTransactionStatusTest extends TestCase
{
    #[Test]
    public function values_method_returns_all_status_values(): void
    {
        $values = PaymentTransactionStatus::values();

        $this->assertSame(['pending', 'held_for_review', 'completed', 'failed', 'rejected'], $values);
    }

    #[Test]
    public function cases_are_string_backed(): void
    {
        $this->assertSame('pending', PaymentTransactionStatus::Pending->value);
        $this->assertSame('held_for_review', PaymentTransactionStatus::HeldForReview->value);
        $this->assertSame('completed', PaymentTransactionStatus::Completed->value);
        $this->assertSame('failed', PaymentTransactionStatus::Failed->value);
        $this->assertSame('rejected', PaymentTransactionStatus::Rejected->value);
    }

    #[Test]
    public function all_cases_can_be_created_from_value(): void
    {
        foreach (PaymentTransactionStatus::cases() as $status) {
            $this->assertSame($status, PaymentTransactionStatus::from($status->value));
        }
    }
}
