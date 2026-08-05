<?php

declare(strict_types=1);

namespace App\Http\Controllers\Receptionist;

use App\Factories\Telemetry\DashboardTelemetryFactory;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
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
     * This method previously re-implemented, query-for-query, the
     * exact same three metrics already computed by ReceptionistDashboardTelemetry — a class
     * that existed but was never called from anywhere. Now delegates to it via
     * DashboardTelemetryFactory (matching the Factory pattern already used across the
     * codebase for role-specific strategy resolution), eliminating the duplicate logic while
     * keeping the existing Inertia prop names ('appointmentCount', 'invoicesCount',
     * 'patientCount') unchanged to avoid any frontend breakage.
     *
     * @return InertiaResponse
     */
    public function index(): InertiaResponse
    {
        $telemetry = DashboardTelemetryFactory::make('receptionist')->getTelemetry(Auth::user());
        $metrics = $telemetry['metrics'];

        return Inertia::render('Receptionist/Dashboard', [
            'appointmentCount' => $metrics['active_appointments'],
            'invoicesCount'    => $metrics['pending_collections'],
            'patientCount'     => $metrics['total_patients'],
        ]);
    }
}
