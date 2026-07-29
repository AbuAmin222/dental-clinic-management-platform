<?php

declare(strict_types=1);

namespace App\Strategies\Authorization\Pricing;

use App\Contracts\Authorization\PricingAuthorizationStrategyInterface;
use App\Models\Pricing;
use App\Models\User;
use App\Policies\Concerns\HasClinicalProfiles;

class ReceptionistAuthorizationStrategy implements PricingAuthorizationStrategyInterface
{
    use HasClinicalProfiles;

    public function authorize(User $user, Pricing $pricing): bool
    {
        return false;
    }
}
