<?php

declare(strict_types=1);

namespace Tests\Unit\States\Invoice;

use App\Enums\InvoiceStatus;
use App\States\Invoice\AbstractInvoiceState;
use App\States\Invoice\CancelledState;
use App\States\Invoice\DraftState;
use App\States\Invoice\PaidState;
use App\States\Invoice\PartiallyPaidState;
use App\States\Invoice\PendingState;
use App\States\Invoice\RefundedState;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AbstractInvoiceStateTest extends TestCase
{
    #[Test]
    public function all_states_implement_invoice_state_interface(): void
    {
        $states = [
            new DraftState(),
            new PendingState(),
            new PartiallyPaidState(),
            new PaidState(),
            new CancelledState(),
            new RefundedState(),
        ];

        foreach ($states as $state) {
            $this->assertInstanceOf(\App\States\Invoice\InvoiceState::class, $state);
        }
    }

    #[Test]
    public function each_state_returns_correct_status(): void
    {
        $this->assertSame(InvoiceStatus::Draft, (new DraftState())->status());
        $this->assertSame(InvoiceStatus::Pending, (new PendingState())->status());
        $this->assertSame(InvoiceStatus::PartiallyPaid, (new PartiallyPaidState())->status());
        $this->assertSame(InvoiceStatus::Paid, (new PaidState())->status());
        $this->assertSame(InvoiceStatus::Cancelled, (new CancelledState())->status());
        $this->assertSame(InvoiceStatus::Refunded, (new RefundedState())->status());
    }

    #[Test]
    public function terminal_states_have_no_allowed_transitions(): void
    {
        $cancelled = new CancelledState();
        $refunded = new RefundedState();

        foreach (InvoiceStatus::cases() as $status) {
            $this->assertFalse($cancelled->canTransitionTo($status));
            $this->assertFalse($refunded->canTransitionTo($status));
        }
    }

    #[Test]
    public function on_enter_default_does_not_throw(): void
    {
        $states = [
            new DraftState(),
            new PendingState(),
            new PartiallyPaidState(),
            new PaidState(),
            new CancelledState(),
            new RefundedState(),
        ];

        $mockInvoice = $this->createMock(Invoice::class);

        foreach ($states as $state) {
            $state->onEnter($mockInvoice);
            $this->assertTrue(true);
        }
    }
}
