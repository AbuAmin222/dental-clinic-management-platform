<?php

declare(strict_types=1);

namespace App\States\Invoice;

use App\Enums\InvoiceStatus;

final class PartiallyPaidState extends AbstractInvoiceState
{
    public function status(): InvoiceStatus
    {
        return InvoiceStatus::PartiallyPaid;
    }

    protected function allowedTransitions(): array
    {
        return [InvoiceStatus::Paid, InvoiceStatus::Refunded, InvoiceStatus::Cancelled];
    }
}
