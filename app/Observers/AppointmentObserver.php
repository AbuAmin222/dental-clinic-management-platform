<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Appointment;

class AppointmentObserver
{
    public function updated(Appointment $appointment): void
    {
        if ($appointment->wasChanged('status') && $appointment->treatment_course_id !== null) {
            $appointment->treatmentCourse->recalculateSessionsCount();
        }
    }
}
