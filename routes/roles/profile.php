<?php

declare(strict_types=1);

use App\Http\Controllers\Profile\UserProfileController;
use Illuminate\Support\Facades\Route;

Route::name('profile.')->group(static function (): void {
    Route::get('/edit', [UserProfileController::class, 'edit'])->name('edit');
    Route::get('/password', [UserProfileController::class, 'password'])->name('password');
    Route::get('/two-factor', [UserProfileController::class, 'twoFactor'])->name('two-factor');
    Route::get('/devices', [UserProfileController::class, 'devices'])->name('devices');
    Route::get('/delete', [UserProfileController::class, 'deleteAccount'])->name('delete');
});
