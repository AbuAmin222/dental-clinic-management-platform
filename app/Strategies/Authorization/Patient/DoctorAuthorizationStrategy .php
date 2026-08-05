<?php

declare(strict_types=1);

namespace App\Strategies\Authorization\Patient;

use App\Contracts\Authorization\PatientAuthorizationStrategyInterface;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use App\Policies\Concerns\HasClinicalProfiles;

class DoctorAuthorizationStrategy implements PatientAuthorizationStrategyInterface
{
    use HasClinicalProfiles;

    /**
     * A doctor may view a patient profile only if a clinical relationship already exists
     * between them — i.e. at least one Appointment record links this doctor to this patient,
     * regardless of that appointment's status (scheduled, confirmed, completed, ...).
     *
     * Confirmed business rule (2026-08-04): covers both "upcoming/confirmed appointments" and
     * "the full historical record with this doctor" in a single, simpler existence check —
     * the broader "any appointment" condition already subsumes the narrower "active appointment
     * only" case.
     */
    public function authorize(User $user, Patient $patient): bool
    {
        $doctorId = $this->getDoctorId($user);

        if ($doctorId === null) {
            return false;
        }

        return Appointment::where('doctor_id', $doctorId)
            ->where('patient_id', $patient->id)
            ->exists();
    }
}
