<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * دورة حياة الفاتورة (State Pattern) — المصدر الوحيد لهذه القيم في كامل المشروع.
 * يُستخدم في: migration الجدول، Invoice::$casts، InvoiceStateFactory، وأي مقارنة حالة.
 */
enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case PartiallyPaid = 'partially_paid';
    case Paid = 'paid';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    /** @return string[] */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
