<?php

declare(strict_types=1);

use App\Http\Controllers\Patient\PatientController as GeneralPatientController;
use App\Http\Controllers\Patient\PatientInvoicePaymentController;
use App\Http\Controllers\Patient\PaymentSandboxController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [GeneralPatientController::class, 'index'])->name('dashboard');

// Self-Service Booking Engines
Route::prefix('appointment')->name('appointment.')->group(static function (): void {
    Route::get('/create', [GeneralPatientController::class, 'createAppointment'])->name('create');
    Route::post('/store', [GeneralPatientController::class, 'storeAppointment'])->name('store');
});

// Invoicing & Point-Of-Sale Terminals
Route::prefix('invoices/{invoice}')->name('invoices.')->group(static function (): void {
    Route::get('/checkout', [GeneralPatientController::class, 'checkoutInvoice'])->name('checkout');
    Route::post('/pay', [PatientInvoicePaymentController::class, 'process'])->name('pay');
});

// Decoupled Payment Integration Core Protocols
Route::prefix('payment')->name('payment.')->group(static function (): void {
    Route::get('/callback/{gateway}/{tx}', [PatientInvoicePaymentController::class, 'callback'])->name('callback');
    Route::get('/sandbox-gateway', [PaymentSandboxController::class, 'showGateway'])->name('sandbox.gateway');
});
