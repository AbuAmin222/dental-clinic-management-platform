<?php

declare(strict_types=1);

namespace App\Policies\Patient;

use App\Enums\Permissions\PatientPermission;
use App\Enums\UserRole;
use App\Factories\Authorization\PatientAuthorizationFactory;
use App\Models\Patient;
use App\Models\User;
use App\Policies\Concerns\HasClinicalProfiles;
use App\Services\Authorization\PermissionGate;
use Illuminate\Auth\Access\HandlesAuthorization;
use InvalidArgumentException;

class PatientPolicy
{
    use HandlesAuthorization, HasClinicalProfiles;

    public function __construct(
        private readonly PermissionGate $gate,
    ) {}

    /**
     * Roles authorized to view generic index/listing views of patients.
     */
    private const ALLOWED_VIEW_ANY_ROLES = [UserRole::Doctor->value, UserRole::Patient->value, UserRole::Receptionist->value, UserRole::Financial->value];

    /**
     * Roles authorized to register a new patient profile.
     */
    private const ALLOWED_CREATE_ROLES = [UserRole::Receptionist->value, UserRole::Admin->value];

    /**
     * Determine whether the user can view any patient listing.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $this->gate->allows($user, UserRole::values(), PatientPermission::ViewAny);
    }

    /**
     * Determine whether the user can view a specific patient profile.
     * Delegates dynamically to role-specific structural strategy classes to adhere to OCP.
     *
     * @param  \App\Models\User     $user
     * @param  \App\Models\Patient  $patient
     * @return bool
     */
    public function view(User $user, Patient $patient): bool
    {
        return $this->gate->allows($user, UserRole::values(), PatientPermission::View)
            && $this->delegateToStrategy($user, $patient);
    }

    /**
     * Determine whether the user can register a new patient profile.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $this->gate->allows($user, self::ALLOWED_CREATE_ROLES, PatientPermission::Create);
    }

    /**
     * Determine whether the user can update an existing patient profile.
     *
     * @param  \App\Models\User     $user
     * @param  \App\Models\Patient  $patient
     * @return bool
     */
    public function update(User $user, Patient $patient): bool
    {
        return $this->gate->allows($user, UserRole::values(), PatientPermission::Update)
            && $this->delegateToStrategy($user, $patient);
    }

    /**
     * Determine whether the user can delete a patient profile.
     *
     * @param  \App\Models\User     $user
     * @param  \App\Models\Patient  $patient
     * @return bool
     */
    public function delete(User $user, Patient $patient): bool
    {
        return $this->gate->allows($user, UserRole::values(), PatientPermission::Delete);
    }

    /**
     * Helper logic to securely process dynamic runtime delegation using the operational
     * authorization factory. 
     * Integrates defensive programming to fail-closed gracefully if
     * configuration mismatches occur.
     *
     * @param  \App\Models\User     $user
     * @param  \App\Models\Patient  $patient
     * @return bool
     */
    private function delegateToStrategy(User $user, Patient $patient): bool
    {
        try {
            return PatientAuthorizationFactory::make($user->role)->authorize($user, $patient);
        } catch (InvalidArgumentException $e) {
            return false;
        }
    }
}
