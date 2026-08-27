<?php

declare(strict_types=1);

namespace Tests\Unit\States\Invoice;

use App\Enums\InvoiceStatus;
use App\States\Invoice\AbstractInvoiceState;
use App\States\Invoice\DraftState;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DraftStateTest extends TestCase
{
    #[Test]
    public function status_returns_draft(): void
    {
        $state = new DraftState();

        $this->assertSame(InvoiceStatus::Draft, $state->status());
    }

    #[Test]
    public function can_transition_to_pending(): void
    {
        $state = new DraftState();

        $this->assertTrue($state->canTransitionTo(InvoiceStatus::Pending));
    }

    #[Test]
    public function can_transition_to_cancelled(): void
    {
        $state = new DraftState();

        $this->assertTrue($state->canTransitionTo(InvoiceStatus::Cancelled));
    }

    #[DataProvider('forbiddenTransitionsProvider')]
    #[Test]
    public function cannot_transition_to_other_states(InvoiceStatus $target): void
    {
        $state = new DraftState();

        $this->assertFalse($state->canTransitionTo($target));
    }

    public static function forbiddenTransitionsProvider(): array
    {
        return [
            'draft' => [InvoiceStatus::Draft],
            'partially_paid' => [InvoiceStatus::PartiallyPaid],
            'paid' => [InvoiceStatus::Paid],
            'refunded' => [InvoiceStatus::Refunded],
        ];
    }

    #[Test]
    public function on_enter_does_not_throw(): void
    {
        $state = new DraftState();

        $state->onEnter($this->createMock(\App\Models\Invoice::class));

        $this->assertTrue(true);
    }
}
