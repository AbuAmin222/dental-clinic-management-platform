<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentTransactionStatus: string
{
    case Pending = 'pending';
    case HeldForReview = 'held_for_review';
    case Completed = 'completed';
    case Failed = 'failed';
    case Rejected = 'rejected';

    /** @return string[] */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
