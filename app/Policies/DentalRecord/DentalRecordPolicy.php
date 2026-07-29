<?php

declare(strict_types=1);

namespace App\Policies\DentalRecord;

use App\Factories\Authorization\DentalRecordAuthorizationFactory;
use App\Models\DentalRecord;
use App\Models\Appointment;
use App\Models\User;
use App\Policies\Concerns\HasClinicalProfiles;
use Illuminate\Auth\Access\HandlesAuthorization;
use InvalidArgumentException;

/**
 * Class DentalRecordPolicy
 * Shields clinical history diagnosis sheets and restricts analytical records to ownership properties.
 */
class DentalRecordPolicy
{
    use HandlesAuthorization, HasClinicalProfiles;

    /**
     * Roles permitted to access structural indexes of corporate dental history.
     */
    private const ALLOWED_VIEW_ANY_ROLES = ['doctor', 'patient'];

    /**
     * Determine whether the user can browse index arrays of medical records.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, self::ALLOWED_VIEW_ANY_ROLES, true);
    }

    /**
     * Determine whether the user can view a specific dental medical record history sheet.
     * Leverages memoized request-level static caches to guarantee ultimate execution speed.
     *
     * @param  \App\Models\User          $user
     * @param  \App\Models\DentalRecord  $dentalRecord
     * @param  \App\Models\Appointment   $appointment
     * @return bool
     */
    public function view(User $user, DentalRecord $dentalRecord, Appointment $appointment): bool
    {
        return $this->delegateToStrategy($user, $dentalRecord, $appointment);
    }

    /**
     * Determine whether a user can formulate new active medical diagnostics or history records.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->role === 'doctor';
    }

    /**
     * Determine whether the user can change clinical content inside an existing medical diagnosis file.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DentalRecord  $dentalRecord
     * @param  \App\Models\Appointment  $appointment
     * @return bool
     */
    public function update(User $user, DentalRecord $dentalRecord, Appointment $appointment): bool
    {
        return $this->delegateToStrategy($user, $dentalRecord, $appointment);
    }

    /**
     * Determine whether the user can soft-delete an existing medical diagnosis file.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DentalRecord  $dentalRecord
     * @param  \App\Models\Appointment  $appointment
     * @return bool
     */
    public function delete(User $user, DentalRecord $dentalRecord, Appointment $appointment): bool
    {
        return $this->delegateToStrategy($user, $dentalRecord, $appointment);
    }

    /**
     * Determine whether the user can restore a soft-deleted dental diagnosis sheet.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DentalRecord  $dentalRecord
     * @param  \App\Models\Appointment  $appointment
     * @return bool
     */
    public function restore(User $user, DentalRecord $dentalRecord, Appointment $appointment): bool
    {
        return $this->delegateToStrategy($user, $dentalRecord, $appointment);
    }

    /**
     * Determine whether clinical history records can be completely hard purged from physical storage systems.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DentalRecord  $dentalRecord
     * @param  \App\Models\Appointment  $appointment
     * @return bool
     */
    public function forceDelete(User $user, DentalRecord $dentalRecord, Appointment $appointment): bool
    {
        return $this->delegateToStrategy($user, $dentalRecord, $appointment);
    }

    /**
     * Helper logic to securely process dynamic runtime delegation using the operational authorization factory.
     * Integrates defensive programming to fail-closed gracefully if configuration mismatches occur.
     *
     * @param  \App\Models\User          $user
     * @param  \App\Models\DentalRecord  $dentalRecord
     * @param  \App\Models\Appointment   $appointment
     * @return bool
     */
    private function delegateToStrategy(User $user, DentalRecord $dentalRecord, Appointment $appointment): bool
    {
        try {
            return DentalRecordAuthorizationFactory::make($user->role)->authorize($user, $dentalRecord, $appointment);
        } catch (InvalidArgumentException $e) {
            return false;
        }
    }
}
