<?php

declare(strict_types=1);

namespace App\Policies\Patient;

use App\Factories\Authorization\PatientAuthorizationFactory;
use App\Models\Patient;
use App\Models\User;
use App\Policies\Concerns\HasClinicalProfiles;
use Illuminate\Auth\Access\HandlesAuthorization;
use InvalidArgumentException;

/**
 * Class PatientPolicy
 *
 * NEW FILE (2026-08-04): Patient records were previously the only clinical/financial entity
 * in the system with zero model-level authorization (Receptionist\PatientController relied
 * solely on the `role:receptionist` route middleware). This Policy closes that gap using the
 * exact same Policy -> Factory -> Strategy architecture already established for
 * Appointment/DentalRecord/Invoice/Pricing (see SERVICE_AUTHORIZATION_UML.md).
 */
class PatientPolicy
{
    use HandlesAuthorization, HasClinicalProfiles;

    /**
     * Roles authorized to view generic index/listing views of patients.
     */
    private const ALLOWED_VIEW_ANY_ROLES = ['doctor', 'patient', 'receptionist'];

    /**
     * Roles authorized to register a new patient profile.
     */
    private const ALLOWED_CREATE_ROLES = ['receptionist'];

    /**
     * Determine whether the user can view any patient listing.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, self::ALLOWED_VIEW_ANY_ROLES, true);
    }

    /**
     * Determine whether the user can view a specific patient profile.
     * Delegates dynamically to role-specific structural strategy classes to adhere to OCP.
     *
     * Confirmed rule (2026-08-04): a doctor may view a patient only if a clinical relationship
     * already exists (at least one Appointment links them, any status) — see
     * Strategies\Authorization\Patient\DoctorAuthorizationStrategy.
     *
     * @param  \App\Models\User     $user
     * @param  \App\Models\Patient  $patient
     * @return bool
     */
    public function view(User $user, Patient $patient): bool
    {
        return $this->delegateToStrategy($user, $patient);
    }

    /**
     * Determine whether the user can register a new patient profile.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return in_array($user->role, self::ALLOWED_CREATE_ROLES, true);
    }

    /**
     * Determine whether the user can update an existing patient profile.
     *
     * CONFIRMED 2026-08-04 (supersedes the 2026-08-04 provisional receptionist-only default):
     * a treating doctor MAY edit the patient's medical profile, not just view it. Delegated
     * through the same Strategy factory as view() — DoctorAuthorizationStrategy's "at least one
     * Appointment exists between this doctor and this patient" rule now governs both view and
     * update access uniformly. Receptionist retains full access via its own Strategy (always
     * true). A patient may update only their own profile (self, via the existing
     * UserService::updateUserProfile() path — this Policy method governs the
     * Receptionist\PatientController path specifically).
     *
     * @param  \App\Models\User     $user
     * @param  \App\Models\Patient  $patient
     * @return bool
     */
    public function update(User $user, Patient $patient): bool
    {
        return $this->delegateToStrategy($user, $patient);
    }

    /**
     * Determine whether the user can delete a patient profile.
     *
     * Provisional default (2026-08-04, not explicitly confirmed): restricted to Receptionist
     * only. No `delete` route currently exists on Receptionist\PatientController, and the
     * `patients` table does not yet have SoftDeletes (see PENDING_TASKS.md Phase 1) — treat
     * this as a hard delete until that migration gap is closed.
     *
     * @param  \App\Models\User     $user
     * @param  \App\Models\Patient  $patient
     * @return bool
     */
    public function delete(User $user, Patient $patient): bool
    {
        return $user->role === 'receptionist';
    }

    /**
     * Helper logic to securely process dynamic runtime delegation using the operational
     * authorization factory. Integrates defensive programming to fail-closed gracefully if
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
