<?php

declare(strict_types=1);

namespace App\Contracts\Authorization;

use App\Models\User;
use App\Models\Patient;

interface PatientAuthorizationStrategyInterface
{
    /**
     * Determine if the user is authorized to interact with the given patient profile asset.
     *
     * @param  \App\Models\User     $user
     * @param  \App\Models\Patient  $patient
     * @return bool
     */
    public function authorize(User $user, Patient $patient): bool;
}
