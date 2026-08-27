<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\RegisterEmailCheckController;
use App\Http\Controllers\Auth\RegisterUsernameCheckController;
// use App\Http\Controllers\Receptionist\PatientController as ReceptionistPatientController;
use Illuminate\Support\Facades\Route;

Route::post('/register/check-email', [RegisterEmailCheckController::class, '__invoke'])
    ->name('register.check-email');


Route::post('check-username', [RegisterUsernameCheckController::class, '__invoke'])
    ->name('check-username');

// Route::post('check-username', [ReceptionistPatientController::class, 'checkUsername'])
//     ->name('check-username');
