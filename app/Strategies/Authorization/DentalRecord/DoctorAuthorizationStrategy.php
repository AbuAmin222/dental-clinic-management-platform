<?php

declare(strict_types=1);

namespace App\Strategies\Authorization\DentalRecord;

use App\Contracts\Authorization\DentalRecordAuthorizationStrategyInterface;
use App\Models\Appointment;
use App\Models\User;
use App\Models\DentalRecord;
use App\Policies\Concerns\HasClinicalProfiles;

class DoctorAuthorizationStrategy implements DentalRecordAuthorizationStrategyInterface
{
    use HasClinicalProfiles;

    public function authorize(User $user, DentalRecord $dentalRecord, Appointment $appointment): bool
    {
        $doctorId = $this->getDoctorId($user);
        $dentaRecordDoctorId = $dentalRecord->doctor_id;
        $appointmentDoctorId = $appointment->doctor_id;

        return $doctorId === $dentaRecordDoctorId && $doctorId === $appointmentDoctorId;
    }
}
