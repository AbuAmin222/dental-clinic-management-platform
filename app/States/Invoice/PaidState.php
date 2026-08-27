<?php

declare(strict_types=1);

namespace App\States\Invoice;

use App\Enums\InvoiceStatus;

final class PaidState extends AbstractInvoiceState
{
    public function status(): InvoiceStatus
    {
        return InvoiceStatus::Paid;
    }

    protected function allowedTransitions(): array
    {
        return [InvoiceStatus::Refunded];
    }
}
