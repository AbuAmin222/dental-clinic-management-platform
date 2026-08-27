<?php

declare(strict_types=1);

namespace App\States\Invoice;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;

interface InvoiceState
{
    public function status(): InvoiceStatus;

    public function canTransitionTo(InvoiceStatus $targetStatus): bool;

    /**
     * Optional side-effect hook fired the moment an invoice enters this state.
     */
    public function onEnter(Invoice $invoice): void;
}
