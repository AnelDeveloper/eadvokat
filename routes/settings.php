<?php

use App\Http\Controllers\Settings\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('postavke', [ProfileController::class, 'edit'])->name('settings.edit');
    Route::patch('postavke/profil', [ProfileController::class, 'update'])->name('settings.profile.update');
    Route::put('postavke/lozinka', [ProfileController::class, 'updatePassword'])
        ->middleware('throttle:6,1')
        ->name('settings.password.update');
});
