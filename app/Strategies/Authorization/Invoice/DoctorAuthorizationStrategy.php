<?php

declare(strict_types=1);

namespace App\Strategies\Authorization\Invoice;

use App\Contracts\Authorization\InvoiceAuthorizationStrategyInterface;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Invoice;
use App\Policies\Concerns\HasClinicalProfiles;

class DoctorAuthorizationStrategy implements InvoiceAuthorizationStrategyInterface
{
    use HasClinicalProfiles;

    public function authorize(User $user, Invoice $invoice, Appointment $appointment): bool
    {
        $doctorId = $this->getDoctorId($user);
        $appointmentId = $appointment->id;

        $invoiceDoctorId = $invoice->doctor_id;
        $invoiceAppointmentId = $invoice->appointment_id;

        return $doctorId === $invoiceDoctorId && $appointmentId === $invoiceAppointmentId;
    }
}
