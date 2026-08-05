<?php

namespace App\Providers;

// use Illuminate\Support\ServiceProvider;
use App\Models\Appointment;
use App\Models\DentalRecord;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Pricing;
use App\Models\User;
use App\Policies\Appointment\AppointmentPolicy;
use App\Policies\DentalRecord\DentalRecordPolicy;
use App\Policies\Patient\PatientPolicy;
use App\Policies\Payment\InvoicePolicy;
use App\Policies\Payment\PricingPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

/**
 * Class AuthServiceProvider
 *
 * CRITICAL FIX: none of the project's Policies live directly under the
 * `App\Policies` namespace that Laravel's convention-based auto-discovery expects
 * (they live in `App\Policies\Appointment`, `App\Policies\DentalRecord`,
 * `App\Policies\Payment`). Auto-discovery silently fails for all four, which means
 * every `$this->authorize(...)` / `Gate::authorize(...)` call in the codebase
 * (DashboardController, DentalRecordController, PricingController, InvoiceController,
 * DentalRecordImageController, Patient\PatientController::checkoutInvoice, UserController...)
 * was throwing AuthorizationException regardless of how correct the underlying
 * Strategy/Factory logic was. This provider closes that gap with explicit registration.
 *
 * @package App\Providers
 */
class AuthServiceProvider extends ServiceProvider
{

    /**
     * The model-to-policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Appointment::class  => AppointmentPolicy::class,
        DentalRecord::class => DentalRecordPolicy::class,
        Invoice::class      => InvoicePolicy::class,
        Patient::class      => PatientPolicy::class,
        Pricing::class      => PricingPolicy::class,
        User::class         => UserPolicy::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
