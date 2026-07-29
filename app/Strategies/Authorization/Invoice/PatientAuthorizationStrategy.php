<?php

declare(strict_types=1);

namespace App\Strategies\Authorization\Invoice;

use App\Contracts\Authorization\InvoiceAuthorizationStrategyInterface;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\User;
use App\Policies\Concerns\HasClinicalProfiles;

class PatientAuthorizationStrategy implements InvoiceAuthorizationStrategyInterface
{
    use HasClinicalProfiles;

    public function authorize(User $user, Invoice $invoice, Appointment $appointment): bool
    {

        return false;
    }
}
