<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['doctor', 'patient', 'receptionist'], true);
    }

    public function view(User $user, Appointment $appointment): bool
    {
        return match ($user->role) {
            'patient' => $user->patient?->id === $appointment->patient_id,
            'doctor'  => $user->doctor?->id === $appointment->doctor_id,
            // Receptionists are clinic-wide front desk staff for now — doctors
            // aren't tied to a department in the schema yet, so there's nothing
            // to scope this to. Revisit if/when doctors get a department_id.
            'receptionist' => true,
            default => false,
        };
    }

    public function create(User $user): bool
    {
        // Patients request their own appointments; receptionists book on a patient's behalf.
        return in_array($user->role, ['patient', 'receptionist'], true);
    }

    public function update(User $user, Appointment $appointment): bool
    {
        return match ($user->role) {
            'doctor'       => $user->doctor?->id === $appointment->doctor_id,
            'receptionist' => true,
            default        => false,
        };
    }

    public function delete(User $user, Appointment $appointment): bool
    {
        return $user->role === 'receptionist';
    }
}
