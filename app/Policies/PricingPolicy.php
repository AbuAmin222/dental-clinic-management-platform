<?php

namespace App\Policies;

use App\Models\Pricing;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PricingPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        // Doctors manage their own list; patients/receptionists need to read it
        // (booking a service, building an invoice).
        return in_array($user->role, ['doctor', 'patient', 'receptionist'], true);
    }

    public function view(User $user, Pricing $pricing): bool
    {
        if ($user->role === 'doctor') {
            return $user->doctor?->id === $pricing->doctor_id;
        }

        // A published price list is effectively public within the clinic.
        return in_array($user->role, ['patient', 'receptionist'], true);
    }

    public function create(User $user): bool
    {
        return $user->role === 'doctor';
    }

    public function update(User $user, Pricing $pricing): bool
    {
        return $user->role === 'doctor' && $user->doctor?->id === $pricing->doctor_id;
    }

    public function delete(User $user, Pricing $pricing): bool
    {
        return $user->role === 'doctor' && $user->doctor?->id === $pricing->doctor_id;
    }

    public function restore(User $user, Pricing $pricing): bool
    {
        return false;
    }

    public function forceDelete(User $user, Pricing $pricing): bool
    {
        return false;
    }
}
