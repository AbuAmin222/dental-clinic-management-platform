<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\InvoiceItem;

/**
 * Keeps Invoice totals in sync with its line items without coupling InvoiceItem
 * to InvoiceService directly (Observer pattern, per the architecture document).
 */
class InvoiceItemObserver
{
    public function created(InvoiceItem $item): void
    {
        $item->invoice->recalculateTotals();
    }

    public function updated(InvoiceItem $item): void
    {
        $item->invoice->recalculateTotals();
    }

    public function deleted(InvoiceItem $item): void
    {
        $item->invoice->recalculateTotals();
    }
}
