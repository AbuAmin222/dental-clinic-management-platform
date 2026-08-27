<?php

declare(strict_types=1);

namespace App\Enums;

enum Gender: string
{
    case Male = 'Male';
    case Female = 'Female';

    /** @return string[] */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
