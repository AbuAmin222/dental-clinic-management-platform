<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Patient;

class ReceptionistController extends Controller
{
    public function index()
    {
        // 1. Active Appointments: Pending, scheduled, or confirmed sessions requiring immediate receptionist action
        $appointmentCount = Appointment::whereIn('status', ['pending', 'scheduled', 'confirmed', 'no_show'])->count();

        // 2. Financial Monitoring: Unpaid or partially paid medical invoices that require collection
        $invoicesCount = Invoice::whereIn('status', ['unpaid', 'partially_paid'])->count();

        // 3. System Growth: Total registered patient files present in the clinic database
        $patientCount = Patient::count();

        return inertia('Receptionist/Dashboard', [
            'appointmentCount' => $appointmentCount,
            'invoicesCount'    => $invoicesCount,
            'patientCount'     => $patientCount,
        ]);
    }
}
