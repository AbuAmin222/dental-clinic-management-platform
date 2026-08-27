<?php

declare(strict_types=1);

namespace App\Routing\Registrars;

use App\Routing\BaseRoleRouteRegistrar;
use App\Http\Controllers\Financial\DashboardController;
use App\Http\Controllers\Financial\InvoiceReviewController;
use App\Http\Controllers\Financial\LocalPaymentMethodController;
use App\Http\Controllers\Financial\OnboardingController;
use App\Http\Controllers\Financial\SalaryPaymentController;
use Illuminate\Routing\Router;

class FinancialRouteRegistrar extends BaseRoleRouteRegistrar
{
    protected function dashboardAction(): array
    {
        return [DashboardController::class, 'index'];
    }

    /**
     * Applied by RoleRouteOrchestrator to the ENTIRE `financial.*` route group, including
     * `/dashboard` and the onboarding routes themselves. This is safe: EnsureOnboardingCompleted
     * exempts the two onboarding route names internally (see the middleware's own
     * EXEMPT_ROUTE_NAMES), so no redirect loop occurs — every other financial route is
     * correctly blocked until the profile is complete, with zero risk of a route being
     * accidentally left unguarded by a future addition to this Registrar.
     */
    public function additionalMiddleware(): array
    {
        return ['onboarding.completed'];
    }

    protected function registerSpecificRoutes(Router $router): void
    {
        $router->prefix('onboarding')->name('onboarding.')->group(static function (Router $group): void {
            $group->get('/', [OnboardingController::class, 'show'])->name('show');
            $group->post('/', [OnboardingController::class, 'store'])->name('store');
        });

        $router->prefix('invoices')->name('invoices.')->group(static function (Router $group): void {
            $group->get('/', [InvoiceReviewController::class, 'index'])->name('index');
            $group->patch('/{invoice}/issue', [InvoiceReviewController::class, 'issue'])->name('issue');
        });

        $router->prefix('payment-methods')->name('paymentMethods.')->group(static function (Router $group): void {
            $group->get('/', [LocalPaymentMethodController::class, 'index'])->name('index');
            $group->post('/', [LocalPaymentMethodController::class, 'store'])->name('store');
            $group->patch('/{localPaymentMethod}', [LocalPaymentMethodController::class, 'update'])->name('update');
            $group->delete('/{localPaymentMethod}', [LocalPaymentMethodController::class, 'destroy'])->name('destroy');
        });

        $router->prefix('salary-payments')->name('salaryPayments.')->group(static function (Router $group): void {
            $group->get('/', [SalaryPaymentController::class, 'index'])->name('index');
            $group->post('/', [SalaryPaymentController::class, 'store'])->name('store');
            $group->patch('/{salaryPayment}/approve', [SalaryPaymentController::class, 'approve'])->name('approve');
            $group->patch('/{salaryPayment}/hold', [SalaryPaymentController::class, 'hold'])->name('hold');
            $group->patch('/{salaryPayment}/cancel', [SalaryPaymentController::class, 'cancel'])->name('cancel');
            $group->patch('/{salaryPayment}/reject', [SalaryPaymentController::class, 'reject'])->name('reject');
            $group->patch('/{salaryPayment}/mark-paid', [SalaryPaymentController::class, 'markPaid'])->name('markPaid');
        });
    }
}
