<?php

declare(strict_types=1);

use App\Http\Controllers\Doctor\DashboardController;
use App\Http\Controllers\Doctor\DentalRecordController;
use App\Http\Controllers\Doctor\DoctorAppointmentController;
use App\Http\Controllers\Doctor\PatientHistoryController;
use App\Http\Controllers\Doctor\PricingController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Pricing Catalog Management Sub-Domain
Route::prefix('pricings')->name('pricings.')->group(static function (): void {
    Route::get('/', [PricingController::class, 'index'])->name('index');
    Route::post('/', [PricingController::class, 'store'])->name('store');
    Route::put('/{pricing}', [PricingController::class, 'update'])->name('update');
    Route::delete('/{pricing}', [PricingController::class, 'destroy'])->name('destroy');
});

// Clinical Treatment & Consultation Scopes
Route::prefix('appointments/{appointment}/dental-record')->name('dentalRecords.')->group(static function (): void {
    Route::get('/create', [DentalRecordController::class, 'create'])->name('create');
    Route::post('/', [DentalRecordController::class, 'store'])->name('store');
});

Route::get('/patients/{patient}/history', [PatientHistoryController::class, 'show'])->name('patients.history');
Route::get('/appointments-archive', [DoctorAppointmentController::class, 'index'])->name('appointments.index');
