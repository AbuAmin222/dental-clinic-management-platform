<?php

declare(strict_types=1);

namespace App\Routing\Registrars;

use App\Routing\BaseRoleRouteRegistrar;
use App\Http\Controllers\Patient\PatientController as GeneralPatientController;
use App\Http\Controllers\Patient\PatientInvoicePaymentController;
use App\Http\Controllers\Patient\PaymentSandboxController;
use Illuminate\Routing\Router;

class PatientRouteRegistrar extends BaseRoleRouteRegistrar
{
    public function additionalMiddleware(): array
    {
        return ['onboarding.completed'];
    }

    protected function dashboardAction(): array
    {
        return [GeneralPatientController::class, 'index'];
    }
    /**
     * Registers self-service booking engines and invoicing systems context.
     */
    protected function registerSpecificRoutes(Router $router): void
    {
        // Self-Service Booking Engines
        $router->prefix('appointment')->name('appointment.')->group(static function (Router $group): void {
            $group->get('/create', [GeneralPatientController::class, 'createAppointment'])->name('create');
            $group->post('/store', [GeneralPatientController::class, 'storeAppointment'])->name('store');
        });

        // Invoicing & Point-Of-Sale Terminals
        $router->prefix('invoices/{invoice}')->name('invoices.')->group(static function (Router $group): void {
            $group->get('/checkout', [GeneralPatientController::class, 'checkoutInvoice'])->name('checkout');
            $group->post('/pay', [PatientInvoicePaymentController::class, 'process'])->name('pay');
        });

        // Decoupled Payment Integration Core Protocols
        $router->prefix('payment')->name('payment.')->group(static function (Router $group): void {
            $group->get('/callback/{gateway}/{tx}', [PatientInvoicePaymentController::class, 'callback'])->name('callback');
            $group->get('/sandbox-gateway', [PaymentSandboxController::class, 'showGateway'])->name('sandbox.gateway');
        });
    }
}
