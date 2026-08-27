<?php

declare(strict_types=1);

namespace App\States\Invoice;

use App\Enums\InvoiceStatus;

/**
 * Terminal state — no further transitions permitted.
 */
final class RefundedState extends AbstractInvoiceState
{
    public function status(): InvoiceStatus
    {
        return InvoiceStatus::Refunded;
    }

    protected function allowedTransitions(): array
    {
        return [];
    }
}
