<?php

use App\Http\Controllers\Auth\RegisterEmailCheckController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\DashboardsController;
use App\Http\Controllers\DentalRecordImageController;
use App\Http\Controllers\ProfileRoleController;

use App\Http\Controllers\Doctor\DashboardController;
use App\Http\Controllers\Doctor\DentalRecordController;
use App\Http\Controllers\Doctor\DoctorAppointmentController;
use App\Http\Controllers\Doctor\PricingController;
use App\Http\Controllers\Doctor\PatientHistoryController;

use App\Http\Controllers\Receptionist\AppointmentController;
use App\Http\Controllers\Receptionist\InvoiceController;
use App\Http\Controllers\Receptionist\PatientController as ReceptionistPatientController;
use App\Http\Controllers\Receptionist\ReceptionistController;

use App\Http\Controllers\Patient\PatientController as GeneralPatientController;
use App\Http\Controllers\Patient\PatientInvoicePaymentController;

use App\Http\Controllers\Profile\UserProfileController;


Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('welcome');

Route::middleware(['throttle:10,1'])->group(function () {
    Route::post('/register/check-email', [RegisterEmailCheckController::class, '__invoke'])
        ->name('register.check-email');

    Route::post('check-username', [ReceptionistPatientController::class, 'checkUsername'])
        ->name('check-username');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    Route::get('/dashboard', [DashboardsController::class, 'index'])->name('dashboard');

    Route::get('/dental-records/{dentalRecord}/xray', [DentalRecordImageController::class, 'show'])
        ->name('dental-records.xray');

    Route::put('/user/profile-role', [ProfileRoleController::class, 'update'])->name('user-profile-role.update');


    Route::middleware(['role:doctor'])->prefix('doctor')->name('doctor.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // 🦷 مسارات إدارة أسعار الخدمات الطبية (Pricing Catalog)
        Route::prefix('pricings')->name('pricings.')->group(function () {
            Route::get('/', [PricingController::class, 'index'])->name('index');
            Route::post('/', [PricingController::class, 'store'])->name('store');
            Route::put('/{pricing}', [PricingController::class, 'update'])->name('update');
            Route::delete('/{pricing}', [PricingController::class, 'destroy'])->name('destroy');
        });

        Route::get('/appointments/{appointment}/dental-record/create', [DentalRecordController::class, 'create'])->name('dentalRecords.create');
        Route::post('/appointments/{appointment}/dental-record', [DentalRecordController::class, 'store'])->name('dentalRecords.store');

        Route::get('/patients/{patient}/history', [PatientHistoryController::class, 'show'])->name('patients.history');

        Route::get('/appointments-archive', [DoctorAppointmentController::class, 'index'])->name('appointments.index');
    });

    Route::middleware(['role:patient'])->prefix('patient')->name('patient.')->group(function () {
        Route::get('/dashboard', [GeneralPatientController::class, 'index'])->name('dashboard');

        Route::get('/appointment/create', [GeneralPatientController::class, 'createAppointment'])->name('appointment.create');
        Route::post('/appointment/store', [GeneralPatientController::class, 'storeAppointment'])->name('appointment.store');

        // مسارات معالجة الدفع الرقمي
        Route::get('/invoices/{invoice}/checkout', [GeneralPatientController::class, 'checkoutInvoice'])->name('invoices.checkout');

        // مسار معالجة الدفع المنطلق من زر الاستمارة في الفروتنيد
        Route::post('/invoices/{invoice}/pay', [PatientInvoicePaymentController::class, 'process'])->name('invoices.pay');

        // مسار الـ Callback الموحد الذي تعود إليه البوابات المحلية الفلسطينية
        Route::get('/payment/callback/{gateway}/{tx}', [PatientInvoicePaymentController::class, 'callback'])->name('payment.callback');
        Route::get('/payment/sandbox-gateway', [App\Http\Controllers\Patient\PaymentSandboxController::class, 'showGateway'])->name('payment.sandbox.gateway');
    });

    Route::middleware(['role:receptionist'])->prefix('receptionist')->name('receptionist.')->group(function () {
        // Dashboard Routes
        Route::get('/dashboard', [ReceptionistController::class, 'index'])->name('dashboard');

        // Patients Routes
        Route::get('/patients', [ReceptionistPatientController::class, 'index'])->name('patients.index');
        Route::get('/patients/create', [ReceptionistPatientController::class, 'create'])->name('patients.create');
        Route::post('/patients/store', [ReceptionistPatientController::class, 'store'])->name('patients.store');
        Route::get('/patients/{patient}', [ReceptionistPatientController::class, 'show'])->name('patients.show');

        // Appointment Routes(Insert, Create, Update)
        Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index'); // 🆕 Main table route
        Route::get('/appointments/create', [AppointmentController::class, 'create'])->name('appointments.create');
        Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
        Route::patch('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.updateStatus'); // 🆕 Change status route

        // 🦷 Invoice Management Routes
        Route::get('/appointments/{appointment}/invoice/create', [InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('/appointments/{appointment}/invoice', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::delete('/appointments/{appointment}/invoice', [InvoiceController::class, 'destroy'])
            ->name('invoices.destroy');
    });
});

Route::middleware(['auth', 'verified'])->prefix('user/profile')->group(function () {
    Route::get('/edit', [UserProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/password', [UserProfileController::class, 'password'])->name('profile.password');
    Route::get('/two-factor', [UserProfileController::class, 'twoFactor'])->name('profile.two-factor');
    Route::get('/devices', [UserProfileController::class, 'devices'])->name('profile.devices');
    Route::get('/delete', [UserProfileController::class, 'deleteAccount'])->name('profile.delete');
});
