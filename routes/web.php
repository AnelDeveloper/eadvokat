<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\MatterController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Landing')->name('home');

Route::middleware('auth')->group(function () {
    // Replaced by the Danas briefing in Phase 4.
    Route::inertia('danas', 'Today')->name('today');

    Route::get('predmeti', [MatterController::class, 'index'])->name('matters.index');
    Route::get('predmeti/{matter}', [MatterController::class, 'show'])->name('matters.show');
    Route::get('predmeti/{matter}/izmjene', [MatterController::class, 'audit'])->name('matters.audit');

    Route::get('dokumenti/{document}', [DocumentController::class, 'show'])->name('documents.show');
    Route::get('dokumenti/{document}/datoteka', [DocumentController::class, 'file'])->name('documents.file');
});

require __DIR__.'/settings.php';
