<?php

declare(strict_types=1);

namespace App\Policies\Appointment;

use App\Factories\Authorization\AppointmentAuthorizationFactory;
use App\Models\Appointment;
use App\Models\User;
use App\Policies\Concerns\HasClinicalProfiles;
use Illuminate\Auth\Access\HandlesAuthorization;
use InvalidArgumentException;

/**
 * Class AppointmentPolicy
 * Dynamically governs clinical scheduling access controls utilizing advanced strategy factories.
 */
class AppointmentPolicy
{
    use HandlesAuthorization, HasClinicalProfiles;

    /**
     * The roles authorized to view generic index matrices of appointments.
     */
    private const ALLOWED_VIEW_ANY_ROLES = ['doctor', 'patient', 'receptionist'];

    /**
     * The roles authorized to initiate and instantiate appointment records.
     */
    private const ALLOWED_CREATE_ROLES = ['patient', 'receptionist'];

    /**
     * Determine whether the user can view any clinical appointments ledger.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, self::ALLOWED_VIEW_ANY_ROLES, true);
    }

    /**
     * Determine whether the user can create new clinic appointments.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return in_array($user->role, self::ALLOWED_CREATE_ROLES, true);
    }

    /**
     * Determine whether the user can view a specific appointment record.
     * Delegates dynamically to role-specific structural strategy classes to adhere to OCP.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Appointment  $appointment
     * @return bool
     */
    public function view(User $user, Appointment $appointment): bool
    {
        return $this->delegateToStrategy($user, $appointment);
    }

    /**
     * Determine whether the user can modify an existing appointment scheduling profile.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Appointment  $appointment
     * @return bool
     */
    public function update(User $user, Appointment $appointment): bool
    {
        return $this->delegateToStrategy($user, $appointment);
    }

    /**
     * Determine whether the user can cancel or completely purge an appointment record.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Appointment  $appointment
     * @return bool
     */
    public function delete(User $user, Appointment $appointment): bool
    {
        return $this->delegateToStrategy($user, $appointment);
    }

    /**
     * Helper logic to securely process dynamic runtime delegation using the operational authorization factory.
     * Integrates defensive programming to fail-closed gracefully if configuration mismatches occur.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Appointment  $appointment
     * @return bool
     */
    private function delegateToStrategy(User $user, Appointment $appointment): bool
    {
        try {
            return AppointmentAuthorizationFactory::make($user->role)->authorize($user, $appointment);
        } catch (InvalidArgumentException $e) {
            return false;
        }
    }
}
