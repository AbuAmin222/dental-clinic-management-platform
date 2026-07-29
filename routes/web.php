<?php

declare(strict_types=1);

use App\Http\Controllers\Clinical\DentalRecordImageController;
use App\Http\Controllers\DashboardsController;
use App\Http\Controllers\PendingReviewController;
use App\Http\Controllers\Profile\ProfileRoleController;
use App\Routing\RoleRouteOrchestrator;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Public Landing Route
|--------------------------------------------------------------------------
*/

Route::get('/', static function () {
    return Inertia::render('Welcome', [
        'canLogin'       => Route::has('login'),
        'canRegister'    => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion'     => PHP_VERSION,
    ]);
})->name('welcome');

/*
|--------------------------------------------------------------------------
| Guest & Public Rate-Limited Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['throttle:10,1'])->group(base_path('routes/roles/auth.php'));

/*
|--------------------------------------------------------------------------
| Authenticated & Verified Domain Segments
|--------------------------------------------------------------------------
*/
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'active',
    'verified',
])->group(static function (Illuminate\Routing\Router $router): void {

    // Global Core Operational Telemetry (Unified Global Route Fallback)
    Route::get('/dashboard', [DashboardsController::class, 'index'])->name('dashboard');
    Route::get('/dental-records/{dentalRecord}/xray', [DentalRecordImageController::class, 'show'])->name('dental-records.xray');
    Route::put('/user/profile-role', [ProfileRoleController::class, 'update'])->name('user-profile-role.update');

    // Cross-Cutting Concerns: Generic Identity Management Layout
    $router->prefix('user/profile')->group(base_path('routes/roles/profile.php'));

    // Dynamic OCP Role Injections Strategy
    RoleRouteOrchestrator::boot($router);
});

Route::middleware(['auth:sanctum', config('jetstream.auth_session')])->group(function () {
    Route::get('/pending-review', [PendingReviewController::class, 'show'])->name('pending-review');
});
