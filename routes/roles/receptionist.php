<?php

declare(strict_types=1);

use App\Http\Controllers\Receptionist\AppointmentController;
use App\Http\Controllers\Receptionist\InvoiceController;
use App\Http\Controllers\Receptionist\PatientController as ReceptionistPatientController;
use App\Http\Controllers\Receptionist\ReceptionistController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [ReceptionistController::class, 'index'])->name('dashboard');

// Structural Patient Registries Domain
Route::prefix('patients')->name('patients.')->group(static function (): void {
    Route::get('/', [ReceptionistPatientController::class, 'index'])->name('index');
    Route::get('/create', [ReceptionistPatientController::class, 'create'])->name('create');
    Route::post('/store', [ReceptionistPatientController::class, 'store'])->name('store');
    Route::get('/{patient}', [ReceptionistPatientController::class, 'show'])->name('show');
});

// Structural Scheduling Registries Domain
Route::prefix('appointments')->name('appointments.')->group(static function (): void {
    Route::get('/', [AppointmentController::class, 'index'])->name('index');
    Route::get('/create', [AppointmentController::class, 'create'])->name('create');
    Route::post('/', [AppointmentController::class, 'store'])->name('store');
    Route::patch('/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('updateStatus');
});

// Clinical Ledger Billing Matrices Domain
Route::prefix('appointments/{appointment}/invoice')->name('invoices.')->group(static function (): void {
    Route::get('/create', [InvoiceController::class, 'create'])->name('create');
    Route::post('/', [InvoiceController::class, 'store'])->name('store');
    Route::delete('/', [InvoiceController::class, 'destroy'])->name('destroy');
});
