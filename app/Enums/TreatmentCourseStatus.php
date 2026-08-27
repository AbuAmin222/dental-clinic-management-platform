<?php

declare(strict_types=1);

namespace App\Enums;

enum TreatmentCourseStatus: string
{
    case Ongoing = 'ongoing';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    /** @return string[] */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
