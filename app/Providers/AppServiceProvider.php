<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Profile\CoreProfileStrategyInterface;
use App\Contracts\Risk\RiskInterceptorInterface;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\InvoiceItem;
use App\Models\Specialization;
use App\Observers\AppointmentObserver;
use App\Observers\InvoiceItemObserver;
use App\Services\Risk\CompositeRiskInterceptor;
use App\Services\Risk\Rules\AmountThresholdRule;
use App\Services\Risk\Rules\TransactionVelocityRule;
use App\Strategies\Profile\CoreProfileStrategy;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Laravel\Fortify\Fortify;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use App\Contracts\Tracer\ExecutionTracerInterface;
use App\Services\Tracer\ExecutionTracer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application core interface services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(CoreProfileStrategyInterface::class, CoreProfileStrategy::class);
        $this->app->singleton(ExecutionTracerInterface::class, ExecutionTracer::class);

        $this->app->singleton(RiskInterceptorInterface::class, static function ($app): CompositeRiskInterceptor {
            return new CompositeRiskInterceptor(
                rules: [
                    $app->make(AmountThresholdRule::class),
                    $app->make(TransactionVelocityRule::class),
                ],
                holdThreshold: (int) config('clinic.risk.hold_threshold', 70),
            );
        });
    }

    /**
     * Bootstrap any application services.
     * * Optimizes application registration processes by rendering static lookup caches
     * for clinical medical structures without triggering overhead database loops.
     *
     * @return void
     */
    public function boot(): void
    {
        InvoiceItem::observe(InvoiceItemObserver::class);
        Appointment::observe(AppointmentObserver::class);

        Fortify::registerView(static function (): InertiaResponse {

            $specializations = Cache::rememberForever('clinic.specializations', static function () {
                return Specialization::select(['id', 'name'])->get()->toArray();
            });

            $departments = Cache::rememberForever('clinic.departments', static function () {
                return Department::select(['id', 'name'])->get()->toArray();
            });

            return Inertia::render('Auth/Register', [
                'specializations' => $specializations,
                'departments'     => $departments,
                'bloodGroups'     => ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'],
            ]);
        });
    }
}
