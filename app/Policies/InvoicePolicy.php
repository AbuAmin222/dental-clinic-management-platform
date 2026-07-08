<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['doctor', 'patient', 'receptionist'], true);
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return match ($user->role) {
            'patient'      => $user->patient?->id === $invoice->patient_id,
            'doctor'       => $user->doctor?->id === $invoice->doctor_id,
            // Billing is a front-desk function clinic-wide for now — same reasoning
            // as AppointmentPolicy, tighten if doctors get department-scoped later.
            'receptionist' => true,
            default        => false,
        };
    }

    public function create(User $user): bool
    {
        return $user->role === 'receptionist';
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->role === 'receptionist';
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->role === 'receptionist';
    }

    public function restore(User $user, Invoice $invoice): bool
    {
        return $user->role === 'receptionist';
    }

    public function forceDelete(User $user, Invoice $invoice): bool
    {
        // Permanently destroying a billing record: reserve for admin tooling later.
        return false;
    }

    /**
     * Custom ability — not one of Laravel's standard 7. Call it explicitly:
     *   $this->authorize('pay', $invoice);
     * Use this inside PatientInvoicePaymentController::process() once it's built,
     * so only the invoice's own patient can ever trigger a charge against it.
     */
    public function pay(User $user, Invoice $invoice): bool
    {
        return $user->role === 'patient' && $user->patient?->id === $invoice->patient_id;
    }
}
