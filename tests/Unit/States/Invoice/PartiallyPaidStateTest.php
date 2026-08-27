<?php

declare(strict_types=1);

namespace Tests\Unit\States\Invoice;

use App\Enums\InvoiceStatus;
use App\States\Invoice\PartiallyPaidState;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PartiallyPaidStateTest extends TestCase
{
    #[Test]
    public function status_returns_partially_paid(): void
    {
        $state = new PartiallyPaidState();

        $this->assertSame(InvoiceStatus::PartiallyPaid, $state->status());
    }

    #[Test]
    public function can_transition_to_paid(): void
    {
        $state = new PartiallyPaidState();

        $this->assertTrue($state->canTransitionTo(InvoiceStatus::Paid));
    }

    #[Test]
    public function can_transition_to_refunded(): void
    {
        $state = new PartiallyPaidState();

        $this->assertTrue($state->canTransitionTo(InvoiceStatus::Refunded));
    }

    #[Test]
    public function can_transition_to_cancelled(): void
    {
        $state = new PartiallyPaidState();

        $this->assertTrue($state->canTransitionTo(InvoiceStatus::Cancelled));
    }

    #[DataProvider('forbiddenTransitionsProvider')]
    #[Test]
    public function cannot_transition_to_other_states(InvoiceStatus $target): void
    {
        $state = new PartiallyPaidState();

        $this->assertFalse($state->canTransitionTo($target));
    }

    public static function forbiddenTransitionsProvider(): array
    {
        return [
            'draft' => [InvoiceStatus::Draft],
            'pending' => [InvoiceStatus::Pending],
            'partially_paid' => [InvoiceStatus::PartiallyPaid],
        ];
    }
}
