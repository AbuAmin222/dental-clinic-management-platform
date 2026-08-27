<?php

declare(strict_types=1);

namespace Tests\Unit\States\Invoice;

use App\Enums\InvoiceStatus;
use App\States\Invoice\PendingState;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PendingStateTest extends TestCase
{
    #[Test]
    public function status_returns_pending(): void
    {
        $state = new PendingState();

        $this->assertSame(InvoiceStatus::Pending, $state->status());
    }

    #[Test]
    public function can_transition_to_partially_paid(): void
    {
        $state = new PendingState();

        $this->assertTrue($state->canTransitionTo(InvoiceStatus::PartiallyPaid));
    }

    #[Test]
    public function can_transition_to_paid(): void
    {
        $state = new PendingState();

        $this->assertTrue($state->canTransitionTo(InvoiceStatus::Paid));
    }

    #[Test]
    public function can_transition_to_cancelled(): void
    {
        $state = new PendingState();

        $this->assertTrue($state->canTransitionTo(InvoiceStatus::Cancelled));
    }

    #[DataProvider('forbiddenTransitionsProvider')]
    #[Test]
    public function cannot_transition_to_other_states(InvoiceStatus $target): void
    {
        $state = new PendingState();

        $this->assertFalse($state->canTransitionTo($target));
    }

    public static function forbiddenTransitionsProvider(): array
    {
        return [
            'draft' => [InvoiceStatus::Draft],
            'pending' => [InvoiceStatus::Pending],
            'refunded' => [InvoiceStatus::Refunded],
        ];
    }
}
