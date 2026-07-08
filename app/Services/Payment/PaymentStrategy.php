<?php

namespace App\Services\Payment;

use App\Models\Invoice;

interface PaymentStrategy
{
    /**
     * تهيئة عملية الدفع وإرجاع بيانات التوجيه أو الـ Session ID
     * * @param Invoice $invoice
     * @param float $amount
     * @return array{success: bool, redirect_url: string, transaction_id: string}
     */
    public function initializePayment(Invoice $invoice, float $amount): array;
}
