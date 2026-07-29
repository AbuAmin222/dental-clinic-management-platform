<?php

declare(strict_types=1);

namespace App\Contracts\Authorization;

use App\Models\Appointment;
use App\Models\User;
use App\Models\Invoice;

interface InvoiceAuthorizationStrategyInterface
{
    /**
     * Determine if the user is authorized to interact with the given invoice asset.
     *
     * @param  \App\Models\User         $user
     * @param  \App\Models\Invoice  $invoice
     * @return bool
     */
    public function authorize(User $user, Invoice $invoice, Appointment $appointment): bool;
}
