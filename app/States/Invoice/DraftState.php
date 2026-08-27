<?php

declare(strict_types=1);

namespace App\States\Invoice;

use App\Enums\InvoiceStatus;

final class DraftState extends AbstractInvoiceState
{
    public function status(): InvoiceStatus
    {
        return InvoiceStatus::Draft;
    }

    protected function allowedTransitions(): array
    {
        return [InvoiceStatus::Pending, InvoiceStatus::Cancelled];
    }
}
