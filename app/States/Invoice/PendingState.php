<?php

declare(strict_types=1);

namespace App\States\Invoice;

use App\Enums\InvoiceStatus;

final class PendingState extends AbstractInvoiceState
{
    public function status(): InvoiceStatus
    {
        return InvoiceStatus::Pending;
    }

    protected function allowedTransitions(): array
    {
        return [InvoiceStatus::PartiallyPaid, InvoiceStatus::Paid, InvoiceStatus::Cancelled];
    }
}
