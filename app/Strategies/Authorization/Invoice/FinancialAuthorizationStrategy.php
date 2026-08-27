<?php

declare(strict_types=1);

namespace App\Strategies\Authorization\Invoice;

use App\Contracts\Authorization\InvoiceAuthorizationStrategyInterface;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\User;

/**
 * Financial officers have unrestricted invoice access (issuance, review, approval),
 * consistent with how ReceptionistAuthorizationStrategy grants unrestricted access in the
 * other domains (Patient, Appointment, etc.) — see ADR-009 for that established precedent.
 */
class FinancialAuthorizationStrategy implements InvoiceAuthorizationStrategyInterface
{
    public function authorize(User $user, Invoice $invoice, Appointment $appointment): bool
    {
        return true;
    }
}
