<?php

declare(strict_types=1);

namespace App\States\Invoice;

use App\Enums\InvoiceStatus;

/**
 * Mirrors the existing PaymentManagerFactory pattern for architectural consistency.
 * ملاحظة مركزية المتغيرات (2026-08-11): المفاتيح هنا الآن InvoiceStatus enum مباشرة،
 * وليست نصوصاً مكررة — إضافة حالة فاتورة جديدة مستقبلاً تعني: حالة جديدة في InvoiceStatus
 * enum + كلاس State جديد + سطر هنا، بدون أي نص حرفي مكرر في أي مكان آخر.
 */
final class InvoiceStateFactory
{
    private const MAP = [
        InvoiceStatus::Draft->value          => DraftState::class,
        InvoiceStatus::Pending->value        => PendingState::class,
        InvoiceStatus::PartiallyPaid->value  => PartiallyPaidState::class,
        InvoiceStatus::Paid->value           => PaidState::class,
        InvoiceStatus::Cancelled->value      => CancelledState::class,
        InvoiceStatus::Refunded->value       => RefundedState::class,
    ];

    public static function make(InvoiceStatus $status): InvoiceState
    {
        $stateClass = self::MAP[$status->value];

        return new $stateClass();
    }
}
