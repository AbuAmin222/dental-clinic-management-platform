<?php

declare(strict_types=1);

namespace App\Strategies\Authorization\Pricing;

use App\Contracts\Authorization\PricingAuthorizationStrategyInterface;
use App\Models\User;
use App\Models\Pricing;
use App\Policies\Concerns\HasClinicalProfiles;

class DoctorAuthorizationStrategy implements PricingAuthorizationStrategyInterface
{
    use HasClinicalProfiles;

    public function authorize(User $user, Pricing $pricing): bool
    {
        $doctorId = $this->getDoctorId($user);

        $priceDoctorId = $pricing->doctor_id;

        return $doctorId === $priceDoctorId;
    }
}
