<?php

declare(strict_types=1);

namespace App\States\Invoice;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;

abstract class AbstractInvoiceState implements InvoiceState
{
    /** @return InvoiceStatus[] */
    abstract protected function allowedTransitions(): array;

    public function canTransitionTo(InvoiceStatus $targetStatus): bool
    {
        return in_array($targetStatus, $this->allowedTransitions(), true);
    }

    public function onEnter(Invoice $invoice): void
    {
        // Default: no side effect. Concrete states override where needed.
    }
}
