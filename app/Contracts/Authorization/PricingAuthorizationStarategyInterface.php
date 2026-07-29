<?php

declare(strict_types=1);

namespace App\Contracts\Authorization;

use App\Models\User;
use App\Models\Pricing;

interface PricingAuthorizationStrategyInterface
{
    /**
     * Determine if the user is authorized to interact with the given pri$pricing asset.
     *
     * @param  \App\Models\User         $user
     * @param  \App\Models\Pricing  $pricing
     * @return bool
     */
    public function authorize(User $user, Pricing $pricing): bool;
}
