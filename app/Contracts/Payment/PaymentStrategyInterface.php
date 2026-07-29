<?php

declare(strict_types=1);

namespace App\Contracts\Payment;

use App\Models\Invoice;

interface PaymentStrategyInterface
{
    /**
     * Initialize payment gateway transaction and resolve gateway redirection context.
     *
     * @param Invoice $invoice
     * @param float $amount
     * @return array{success: bool, redirect_url: string, transaction_id: string}
     */
    public function initializePayment(Invoice $invoice, float $amount): array;
}
