<?php

declare(strict_types=1);

namespace App\Policies\Payment;

use App\Factories\Authorization\PricingAuthorizationFactory;
use App\Models\Pricing;
use App\Models\User;
use App\Policies\Concerns\HasClinicalProfiles;
use Illuminate\Auth\Access\HandlesAuthorization;
use InvalidArgumentException;

/**
 * Class PricingPolicy
 * Secures dynamic doctor-scoped service pricing rates and regulates catalog adjustments.
 */
class PricingPolicy
{
    use HandlesAuthorization, HasClinicalProfiles;

    /**
     * Roles allowed to inspect clinical procedure catalogs.
     */
    private const ALLOWED_VIEW_ANY_ROLES = ['doctor', 'patient', 'receptionist'];

    /**
     * Roles allowed read-only access to specific price cards without dynamic check requirements.
     */
    private const ALLOWED_READ_ONLY_ROLES = ['patient', 'receptionist'];

    /**
     * Determine whether the user can read the catalogs listing corporate treatment pricing schemas.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, self::ALLOWED_VIEW_ANY_ROLES, true);
    }

    /**
     * Determine whether the user can observe a targeted standard procedural pricing index card.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Pricing  $pricing
     * @return bool
     */
    public function view(User $user, Pricing $pricing): bool
    {
        if ($user->role === 'doctor') {
            return $this->delegateToStrategy($user, $pricing);;
        }

        return in_array($user->role, self::ALLOWED_READ_ONLY_ROLES, true);
    }

    /**
     * Determine whether the user can declare a new dynamic service pricing record.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->role === 'doctor';
    }

    /**
     * Determine whether the user can change price points inside their dynamic profile lookup cards.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Pricing  $pricing
     * @return bool
     */
    public function update(User $user, Pricing $pricing): bool
    {
        return $user->role === 'doctor' && $this->delegateToStrategy($user, $pricing);
    }

    /**
     * Determine whether the user can decommission or delete an obsolete medical pricing tier card.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Pricing  $pricing
     * @return bool
     */
    public function delete(User $user, Pricing $pricing): bool
    {
        return $user->role === 'doctor' && $this->delegateToStrategy($user, $pricing);
    }

    /**
     * Determine whether the user can restore a soft-deleted dynamic pricing tier record.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Pricing  $pricing
     * @return bool
     */
    public function restore(User $user, Pricing $pricing): bool
    {
        return $user->role === 'doctor' && $this->delegateToStrategy($user, $pricing);
    }

    /**
     * Determine whether corporate dynamic pricing cards can be hard purged from the architecture.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Pricing  $pricing
     * @return bool
     */
    public function forceDelete(User $user, Pricing $pricing): bool
    {
        return false;
    }

    /**
     * Helper logic to securely process dynamic runtime delegation using the operational authorization factory.
     * Integrates defensive programming to fail-closed gracefully if configuration mismatches occur.
     *
     * @param  \App\Models\User          $user
     * @param  \App\Models\Pricing  $pricing
     * @return bool
     */
    private function delegateToStrategy(User $user, Pricing $pricing): bool
    {
        try {
            return PricingAuthorizationFactory::make($user->role)->authorize($user, $pricing);
        } catch (InvalidArgumentException $e) {
            return false;
        }
    }
}
