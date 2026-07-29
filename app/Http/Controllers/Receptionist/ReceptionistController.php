<?php

declare(strict_types=1);

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Patient;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Class ReceptionistController
 *
 * Compiles structural widgets and administrative dashboard counts.
 *
 * @package App\Http\Controllers\Receptionist
 */
class ReceptionistController extends Controller
{
    /**
     * Map organizational system metrics.
     *
     * @return InertiaResponse
     */
    public function index(): InertiaResponse
    {
        $appointmentCount = Appointment::whereIn('status', ['pending', 'scheduled', 'confirmed', 'no_show'])->count();
        $invoicesCount    = Invoice::whereIn('status', ['unpaid', 'partially_paid'])->count();
        $patientCount     = Patient::count();

        return Inertia::render('Receptionist/Dashboard', [
            'appointmentCount' => $appointmentCount,
            'invoicesCount'    => $invoicesCount,
            'patientCount'     => $patientCount,
        ]);
    }
}
