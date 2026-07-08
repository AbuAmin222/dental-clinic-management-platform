<?php

namespace App\Policies;

use App\Models\DentalRecord;
use App\Models\User;

class DentalRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['doctor', 'patient'], true);
    }

    public function view(User $user, DentalRecord $dentalRecord): bool
    {
        return match ($user->role) {
            'doctor'  => $user->doctor?->id === $dentalRecord->doctor_id,
            'patient' => $user->patient?->id === $dentalRecord->patient_id,
            // Receptionists don't get clinical detail by default — they handle
            // scheduling/billing, not diagnoses. Flip this if front-desk needs it.
            default => false,
        };
    }

    public function create(User $user): bool
    {
        return $user->role === 'doctor';
    }

    public function update(User $user, DentalRecord $dentalRecord): bool
    {
        return $user->role === 'doctor' && $user->doctor?->id === $dentalRecord->doctor_id;
    }

    public function delete(User $user, DentalRecord $dentalRecord): bool
    {
        return $user->role === 'doctor' && $user->doctor?->id === $dentalRecord->doctor_id;
    }

    public function restore(User $user, DentalRecord $dentalRecord): bool
    {
        return $user->role === 'doctor' && $user->doctor?->id === $dentalRecord->doctor_id;
    }

    public function forceDelete(User $user, DentalRecord $dentalRecord): bool
    {
        // Permanently destroying a medical record: reserve for admin tooling later.
        return false;
    }
}
