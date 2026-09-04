<?php

declare(strict_types=1);

namespace App\Policies\Appointment;

use App\Enums\Permissions\AppointmentPermission;
use App\Enums\UserRole;
use App\Factories\Authorization\AppointmentAuthorizationFactory;
use App\Models\Appointment;
use App\Models\User;
use App\Policies\Concerns\HasClinicalProfiles;
use App\Services\Authorization\PermissionGate;
use Illuminate\Auth\Access\HandlesAuthorization;
use InvalidArgumentException;

/**
 * Class AppointmentPolicy
 * Dynamically governs clinical scheduling access controls utilizing advanced strategy factories.
 */
class AppointmentPolicy
{
    use HandlesAuthorization, HasClinicalProfiles;

    public function __construct(
        private readonly PermissionGate $gate,
    ) {}


    /**
     * The roles authorized to initiate and instantiate appointment records.
     */
    private const ALLOWED_CREATE_ROLES = [UserRole::Patient->value, UserRole::Receptionist->value];

    /**
     * Determine whether the user can view any clinical appointments ledger.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $this->gate->allows($user, UserRole::values(), AppointmentPermission::ViewAny);
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
        return $this->gate->allows($user, UserRole::values(), AppointmentPermission::View)
            && $this->delegateToStrategy($user, $appointment);
    }

    /**
     * Determine whether the user can create new clinic appointments.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $this->gate->allows($user, self::ALLOWED_CREATE_ROLES, AppointmentPermission::Create);
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
        return $this->gate->allows($user, self::ALLOWED_CREATE_ROLES, AppointmentPermission::Update)
            && $this->delegateToStrategy($user, $appointment);
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
        return $this->gate->allows($user, UserRole::staffRoleValues(), AppointmentPermission::Delete);
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
