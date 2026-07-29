<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Profile\CoreProfileStrategyInterface;
use App\Models\Department;
use App\Models\Specialization;
use App\Strategies\Profile\CoreProfileStrategy;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Laravel\Fortify\Fortify;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

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
        Fortify::registerView(static function (): InertiaResponse {

            // Fetch and indefinitely cache global medical specializations (optimized payload columns)
            $specializations = Cache::rememberForever('clinic.specializations', static function () {
                return Specialization::select(['id', 'name'])->get()->toArray();
            });

            // Fetch and indefinitely cache global administrative departments
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
