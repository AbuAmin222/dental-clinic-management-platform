<?php

declare(strict_types=1);

namespace App\Services\PaymentService;

use App\Models\FinancialAuditLog;
use App\Models\Invoice;
use App\Models\User;

/**
 * Thin, single-purpose helper so every Financial-domain Controller/Service records audit
 * entries the same way, instead of each call site constructing FinancialAuditLog::create()
 * arrays by hand (which would drift in shape over time).
 */
class FinancialAuditLogger
{
    public function log(User $actor, string $action, ?Invoice $invoice = null, ?float $amountChanged = null, ?array $before = null, ?array $after = null, ?string $ip = null): FinancialAuditLog
    {
        return FinancialAuditLog::create([
            'financial_id'    => $actor->financial?->id,
            'invoice_id'      => $invoice?->id,
            'action'          => $action,
            'amount_changed'  => $amountChanged,
            'payload_before'  => $before,
            'payload_after'   => $after,
            'ip_address'      => $ip,
        ]);
    }
}
