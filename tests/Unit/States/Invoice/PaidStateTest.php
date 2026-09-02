<?php

declare(strict_types=1);

namespace Tests\Unit\States\Invoice;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\States\Invoice\PaidState;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PaidStateTest extends TestCase
{
    #[Test]
    public function status_returns_paid(): void
    {
        $state = new PaidState();

        $this->assertSame(InvoiceStatus::Paid, $state->status());
    }

    #[Test]
    public function can_transition_to_refunded(): void
    {
        $state = new PaidState();

        $this->assertTrue($state->canTransitionTo(InvoiceStatus::Refunded));
    }

    #[Test]
    public function cannot_transition_to_non_refunded_states(): void
    {
        $state = new PaidState();

        $forbidden = [
            InvoiceStatus::Draft,
            InvoiceStatus::Pending,
            InvoiceStatus::PartiallyPaid,
            InvoiceStatus::Cancelled,
        ];

        foreach ($forbidden as $target) {
            $this->assertFalse(
                $state->canTransitionTo($target),
                "PaidState should not allow transition to {$target->value}"
            );
        }
    }
}
