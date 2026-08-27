<?php

declare(strict_types=1);

namespace Tests\Unit\States\Invoice;

use App\Enums\InvoiceStatus;
use App\States\Invoice\RefundedState;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RefundedStateTest extends TestCase
{
    #[Test]
    public function status_returns_refunded(): void
    {
        $state = new RefundedState();

        $this->assertSame(InvoiceStatus::Refunded, $state->status());
    }

    #[Test]
    public function cannot_transition_to_any_state(): void
    {
        $state = new RefundedState();

        foreach (InvoiceStatus::cases() as $status) {
            $this->assertFalse(
                $state->canTransitionTo($status),
                "RefundedState should not allow any transition, but found transition to {$status->value}"
            );
        }
    }

    #[Test]
    public function on_enter_does_not_throw(): void
    {
        $state = new RefundedState();

        $state->onEnter($this->createMock(\App\Models\Invoice::class));

        $this->assertTrue(true);
    }
}
