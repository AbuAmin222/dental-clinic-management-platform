<?php

declare(strict_types=1);

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

final class MoneyCast implements CastsAttributes
{
    private readonly int $minorUnitFactor;

    public function __construct()
    {
        $this->minorUnitFactor = (int) config('clinic.money.minor_unit_factor', 100);
    }

    public function get($model, string $key, $value, array $attributes): ?float
    {
        if ($value === null) {
            return null;
        }

        return round(((int) $value) / $this->minorUnitFactor, 2);
    }

    public function set($model, string $key, $value, array $attributes): ?int
    {
        if ($value === null) {
            return null;
        }

        return (int) round(((float) $value) * $this->minorUnitFactor);
    }
}
