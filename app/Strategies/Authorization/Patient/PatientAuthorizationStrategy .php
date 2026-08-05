<?php

declare(strict_types=1);

namespace App\Strategies\Authorization\Patient;

use App\Contracts\Authorization\PatientAuthorizationStrategyInterface;
use App\Models\Patient;
use App\Models\User;
use App\Policies\Concerns\HasClinicalProfiles;

class PatientAuthorizationStrategy implements PatientAuthorizationStrategyInterface
{
    use HasClinicalProfiles;

    /**
     * A patient may only view/act on their own profile record.
     */
    public function authorize(User $user, Patient $patient): bool
    {
        return $this->getPatientId($user) === $patient->id;
    }
}
