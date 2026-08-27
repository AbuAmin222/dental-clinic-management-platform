<?php

declare(strict_types=1);

namespace Tests\Unit\States\Invoice;

use App\Enums\InvoiceStatus;
use App\States\Invoice\InvoiceStateFactory;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class InvoiceStateFactoryTest extends TestCase
{
    #[Test]
    public function make_returns_draft_state_for_draft_status(): void
    {
        $state = InvoiceStateFactory::make(InvoiceStatus::Draft);

        $this->assertInstanceOf(\App\States\Invoice\DraftState::class, $state);
    }

    #[Test]
    public function make_returns_pending_state_for_pending_status(): void
    {
        $state = InvoiceStateFactory::make(InvoiceStatus::Pending);

        $this->assertInstanceOf(\App\States\Invoice\PendingState::class, $state);
    }

    #[Test]
    public function make_returns_partially_paid_state_for_partially_paid_status(): void
    {
        $state = InvoiceStateFactory::make(InvoiceStatus::PartiallyPaid);

        $this->assertInstanceOf(\App\States\Invoice\PartiallyPaidState::class, $state);
    }

    #[Test]
    public function make_returns_paid_state_for_paid_status(): void
    {
        $state = InvoiceStateFactory::make(InvoiceStatus::Paid);

        $this->assertInstanceOf(\App\States\Invoice\PaidState::class, $state);
    }

    #[Test]
    public function make_returns_cancelled_state_for_cancelled_status(): void
    {
        $state = InvoiceStateFactory::make(InvoiceStatus::Cancelled);

        $this->assertInstanceOf(\App\States\Invoice\CancelledState::class, $state);
    }

    #[Test]
    public function make_returns_refunded_state_for_refunded_status(): void
    {
        $state = InvoiceStateFactory::make(InvoiceStatus::Refunded);

        $this->assertInstanceOf(\App\States\Invoice\RefundedState::class, $state);
    }

    #[Test]
    public function make_throws_for_every_valid_status(): void
    {
        foreach (InvoiceStatus::cases() as $status) {
            $state = InvoiceStateFactory::make($status);

            $this->assertInstanceOf(\App\States\Invoice\InvoiceState::class, $state);
        }
    }
}
