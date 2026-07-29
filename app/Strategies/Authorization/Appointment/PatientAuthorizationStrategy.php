<?php

declare(strict_types=1);

namespace App\Strategies\Authorization\Appointment;

use App\Contracts\Authorization\AppointmentAuthorizationStrategyInterface;
use App\Models\User;
use App\Models\Appointment;
use App\Policies\Concerns\HasClinicalProfiles;

class PatientAuthorizationStrategy implements AppointmentAuthorizationStrategyInterface
{
    use HasClinicalProfiles;

    public function authorize(User $user, Appointment $appointment): bool
    {
        return $this->getPatientId($user) === $appointment->patient_id;
    }
}
