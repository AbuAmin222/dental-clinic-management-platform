<?php

declare(strict_types=1);

namespace App\Routing\Registrars;

use App\Routing\BaseRoleRouteRegistrar;
use App\Http\Controllers\Doctor\DentalRecordController;
use App\Http\Controllers\Doctor\DoctorAppointmentController;
use App\Http\Controllers\Doctor\PatientHistoryController;
use App\Http\Controllers\Doctor\PricingController;
use Illuminate\Routing\Router;
use App\Http\Controllers\Doctor\DashboardController;

class DoctorRouteRegistrar extends BaseRoleRouteRegistrar
{
    public function additionalMiddleware(): array
    {
        return ['onboarding.completed'];
    }

    protected function dashboardAction(): array
    {
        return [DashboardController::class, 'index'];
    }
    /**
     * Registers the specialized clinical medical operations context.
     */
    protected function registerSpecificRoutes(Router $router): void
    {
        // Pricing Catalog Management Sub-Domain
        $router->prefix('pricings')->name('pricings.')->group(static function (Router $group): void {
            $group->get('/', [PricingController::class, 'index'])->name('index');
            $group->post('/', [PricingController::class, 'store'])->name('store');
            $group->put('/{pricing}', [PricingController::class, 'update'])->name('update');
            $group->delete('/{pricing}', [PricingController::class, 'destroy'])->name('destroy');
        });

        // Clinical Treatment & Consultation Scopes
        $router->prefix('appointments/{appointment}/dental-record')->name('dentalRecords.')->group(static function (Router $group): void {
            $group->get('/create', [DentalRecordController::class, 'create'])->name('create');
            $group->post('/', [DentalRecordController::class, 'store'])->name('store');
        });

        $router->get('/patients/{patient}/history', [PatientHistoryController::class, 'show'])->name('patients.history');
        $router->get('/appointments-archive', [DoctorAppointmentController::class, 'index'])->name('appointments.index');
    }
}
