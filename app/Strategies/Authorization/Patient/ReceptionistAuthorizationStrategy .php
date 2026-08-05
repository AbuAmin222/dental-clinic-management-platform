<?php

declare(strict_types=1);

namespace App\Strategies\Authorization\Patient;

use App\Contracts\Authorization\PatientAuthorizationStrategyInterface;
use App\Models\Patient;
use App\Models\User;
use App\Policies\Concerns\HasClinicalProfiles;

class ReceptionistAuthorizationStrategy implements PatientAuthorizationStrategyInterface
{
    use HasClinicalProfiles;

    /**
     * Front-desk staff require unrestricted read access to patient profiles to perform
     * registration, scheduling, and billing duties — consistent with the Receptionist
     * strategy's behavior across the other three domains (Appointment/Invoice/Pricing).
     */
    public function authorize(User $user, Patient $patient): bool
    {
        return true;
    }
}
