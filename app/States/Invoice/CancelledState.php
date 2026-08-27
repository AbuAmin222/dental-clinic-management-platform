<?php

declare(strict_types=1);

namespace App\States\Invoice;

use App\Enums\InvoiceStatus;

/**
 * Terminal state — no further transitions permitted.
 */
final class CancelledState extends AbstractInvoiceState
{
    public function status(): InvoiceStatus
    {
        return InvoiceStatus::Cancelled;
    }

    protected function allowedTransitions(): array
    {
        return [];
    }
}
