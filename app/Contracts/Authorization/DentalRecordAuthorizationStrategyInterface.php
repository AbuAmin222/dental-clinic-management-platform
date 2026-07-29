<?php

declare(strict_types=1);

namespace App\Contracts\Authorization;

use App\Models\User;
use App\Models\DentalRecord;
use App\Models\Appointment;

interface DentalRecordAuthorizationStrategyInterface
{
    /**
     * Determine if the user is authorized to interact with the given dental Record asset.
     *
     * @param  \App\Models\User          $user
     * @param  \App\Models\DentalRecord  $dentalRecord
     * @param  \App\Models\Appointment   $appointment
     * @return bool
     */
    public function authorize(User $user, DentalRecord $dentalRecord, Appointment $appointment): bool;
}
