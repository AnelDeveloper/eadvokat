<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\MatterController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Landing')->name('home');

Route::middleware('auth')->group(function () {
    // Replaced by the Danas briefing in Phase 4.
    Route::inertia('danas', 'Today')->name('today');

    Route::get('predmeti', [MatterController::class, 'index'])->name('matters.index');
    Route::get('predmeti/{matter}', [MatterController::class, 'show'])->name('matters.show');
    Route::get('predmeti/{matter}/izmjene', [MatterController::class, 'audit'])->name('matters.audit');

    Route::get('posta', [MailController::class, 'index'])->name('mail.index');
    Route::get('posta/fotografisi', [MailController::class, 'capture'])->name('mail.capture');
    Route::post('posta/fotografisi', [MailController::class, 'store'])->name('mail.store');
    Route::get('posta/pregled/{document}', [MailController::class, 'review'])->name('mail.review');
    Route::post('posta/pregled/{document}', [MailController::class, 'save'])->name('mail.save');

    Route::get('dokumenti/{document}', [DocumentController::class, 'show'])->name('documents.show');
    Route::get('dokumenti/{document}/datoteka', [DocumentController::class, 'file'])->name('documents.file');
});

require __DIR__.'/settings.php';
