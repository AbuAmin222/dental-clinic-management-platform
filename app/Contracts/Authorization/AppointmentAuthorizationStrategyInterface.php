<?php

declare(strict_types=1);

namespace App\Contracts\Authorization;

use App\Models\User;
use App\Models\Appointment;

interface AppointmentAuthorizationStrategyInterface
{
    /**
     * Determine if the user is authorized to interact with the given appointment asset.
     *
     * @param  \App\Models\User         $user
     * @param  \App\Models\Appointment  $appointment
     * @return bool
     */
    public function authorize(User $user, Appointment $appointment): bool;
}
