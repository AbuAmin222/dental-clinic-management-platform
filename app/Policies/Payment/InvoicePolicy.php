<?php

declare(strict_types=1);

namespace App\Policies\Payment;

use App\Factories\Authorization\InvoiceAuthorizationFactory;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\User;
use App\Policies\Concerns\HasClinicalProfiles;
use Illuminate\Auth\Access\HandlesAuthorization;
use InvalidArgumentException;

/**
 * Class InvoicePolicy
 * Manages accounting safeguards, clinical billing definitions, and financial balance modification protections.
 */
class InvoicePolicy
{
    use HandlesAuthorization, HasClinicalProfiles;

    /**
     * Roles permitted to pull general accounting index ledgers.
     */
    private const ALLOWED_VIEW_ANY_ROLES = ['doctor', 'patient', 'receptionist'];

    /**
     * Determine whether the user can view billing arrays.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, self::ALLOWED_VIEW_ANY_ROLES, true);
    }

    /**
     * Determine whether the user can review financial structural parameters of an invoice.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Invoice  $invoice
     * @param  \App\Models\Appointment  $appointment
     * @return bool
     */
    public function view(User $user, Invoice $invoice, Appointment $appointment): bool
    {
        return $this->delegateToStrategy($user, $invoice, $appointment);
    }

    /**
     * Determine whether the user can compute or create a new invoice payload.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->role === 'receptionist';
    }

    /**
     * Determine whether the user can change corporate billing configurations or adjust prices.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Invoice  $invoice
     * @param  \App\Models\Appointment  $appointment
     * @return bool
     */
    public function update(User $user, Invoice $invoice, Appointment $appointment): bool
    {
        return $user->role === 'receptionist' && $this->delegateToStrategy($user, $invoice, $appointment);
    }

    /**
     * Determine whether the user can void or soft-delete financial invoices.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Invoice  $invoice
     * @param  \App\Models\Appointment  $appointment
     * @return bool
     */
    public function delete(User $user, Invoice $invoice, Appointment $appointment): bool
    {
        return $user->role === 'receptionist' && $this->delegateToStrategy($user, $invoice, $appointment);
    }

    /**
     * Determine whether the user can restore a voided invoice structure.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Invoice  $invoice
     * @param  \App\Models\Appointment  $appointment
     * @return bool
     */
    public function restore(User $user, Invoice $invoice, Appointment $appointment): bool
    {
        return $user->role === 'receptionist' && $this->delegateToStrategy($user, $invoice, $appointment);
    }

    /**
     * Determine whether financial parameters can be completely hard purged from accounting records.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Invoice  $invoice
     * @return bool
     */
    public function forceDelete(User $user, Invoice $invoice): bool
    {
        return false;
    }

    /**
     * Custom programmatic scope determining whether a patient entity can safely execute real-time payment procedures.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Invoice  $invoice
     * @param  \App\Models\Appointment  $appointment
     * @return bool
     */
    public function pay(User $user, Invoice $invoice, Appointment $appointment): bool
    {
        return $user->role === 'patient' && $this->delegateToStrategy($user, $invoice, $appointment);
    }

    /**
     * Helper logic to securely process dynamic runtime delegation using the operational authorization factory.
     * Integrates defensive programming to fail-closed gracefully if configuration mismatches occur.
     *
     * @param  \App\Models\User          $user
     * @param  \App\Models\Invoice  $invoice
     * @param  \App\Models\Appointment   $appointment
     * @return bool
     */
    private function delegateToStrategy(User $user, Invoice $invoice, Appointment $appointment): bool
    {
        try {
            return InvoiceAuthorizationFactory::make($user->role)->authorize($user, $invoice, $appointment);
        } catch (InvalidArgumentException $e) {
            return false;
        }
    }
}
