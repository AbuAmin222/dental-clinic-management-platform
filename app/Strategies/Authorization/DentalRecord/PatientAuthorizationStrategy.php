<?php

declare(strict_types=1);

namespace App\Strategies\Authorization\DentalRecord;

use App\Contracts\Authorization\DentalRecordAuthorizationStrategyInterface;
use App\Models\Appointment;
use App\Models\User;
use App\Models\DentalRecord;
use App\Policies\Concerns\HasClinicalProfiles;

class PatientAuthorizationStrategy implements DentalRecordAuthorizationStrategyInterface
{
    use HasClinicalProfiles;

    public function authorize(User $user, DentalRecord $dentalRecord, Appointment $appointment): bool
    {
        $patientId = $this->getPatientId($user);
        $dentaRecordPatientId = $dentalRecord->patient_id;
        $appointmentPatientId = $appointment->patient_id;

        return $patientId === $dentaRecordPatientId && $patientId === $appointmentPatientId;
    }
}
