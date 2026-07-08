<?php

namespace App\Providers;

use App\Models\Department;
use App\Models\Specialization;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Laravel\Fortify\Fortify;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Fortify::registerView(function () {
            $specializations = Cache::rememberForever('clinic.specializations', function () {
                return Specialization::select('id', 'name')->get();
            });

            $departments = Cache::rememberForever('clinic.departments', function () {
                return Department::select('id', 'name')->get();
            });

            return Inertia::render('Auth/Register', [
                'specializations' => $specializations,
                'departments' => $departments,
                'bloodGroups' => ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'],
            ]);
        });
    }
}
