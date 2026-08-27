<?php

declare(strict_types=1);

namespace App\Enums;

enum SalaryPaymentStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Held = 'held';
    case Paid = 'paid';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    /** @return string[] */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
