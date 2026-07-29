<?php

declare(strict_types=1);

namespace App\Routing\Registrars;

use App\Routing\BaseRoleRouteRegistrar;
use App\Http\Controllers\Receptionist\AppointmentController;
use App\Http\Controllers\Receptionist\InvoiceController;
use App\Http\Controllers\Receptionist\PatientController as ReceptionistPatientController;
use App\Http\Controllers\Receptionist\ReceptionistController;
use Illuminate\Routing\Router;

class ReceptionistRouteRegistrar extends BaseRoleRouteRegistrar
{

    protected function dashboardAction(): array
    {
        return [ReceptionistController::class, 'index'];
    }
    /**
     * Registers the dedicated high-performance receptionist operational matrix.
     */
    protected function registerSpecificRoutes(Router $router): void
    {
        // Structural Patient Registries Domain
        $router->prefix('patients')->name('patients.')->group(static function (Router $group): void {
            $group->get('/', [ReceptionistPatientController::class, 'index'])->name('index');
            $group->get('/create', [ReceptionistPatientController::class, 'create'])->name('create');
            $group->post('/store', [ReceptionistPatientController::class, 'store'])->name('store');
            $group->get('/{patient}', [ReceptionistPatientController::class, 'show'])->name('show');
        });

        // Structural Scheduling Registries Domain
        $router->prefix('appointments')->name('appointments.')->group(static function (Router $group): void {
            $group->get('/', [AppointmentController::class, 'index'])->name('index');
            $group->get('/create', [AppointmentController::class, 'create'])->name('create');
            $group->post('/', [AppointmentController::class, 'store'])->name('store');
            $group->patch('/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('updateStatus');
        });

        // Clinical Ledger Billing Matrices Domain
        $router->prefix('appointments/{appointment}/invoice')->name('invoices.')->group(static function (Router $group): void {
            $group->get('/create', [InvoiceController::class, 'create'])->name('create');
            $group->post('/', [InvoiceController::class, 'store'])->name('store');
            $group->delete('/', [InvoiceController::class, 'destroy'])->name('destroy');
        });
    }
}
