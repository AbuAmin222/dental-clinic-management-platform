<?php

declare(strict_types=1);

namespace Tests\Unit\States\Invoice;

use App\Enums\InvoiceStatus;
use App\States\Invoice\CancelledState;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CancelledStateTest extends TestCase
{
    #[Test]
    public function status_returns_cancelled(): void
    {
        $state = new CancelledState();

        $this->assertSame(InvoiceStatus::Cancelled, $state->status());
    }

    #[Test]
    public function cannot_transition_to_any_state(): void
    {
        $state = new CancelledState();

        foreach (InvoiceStatus::cases() as $status) {
            $this->assertFalse(
                $state->canTransitionTo($status),
                "CancelledState should not allow any transition, but found transition to {$status->value}"
            );
        }
    }

    #[Test]
    public function on_enter_does_not_throw(): void
    {
        $state = new CancelledState();

        $state->onEnter($this->createMock(\App\Models\Invoice::class));

        $this->assertTrue(true);
    }
}
