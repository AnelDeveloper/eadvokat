<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Landing')->name('home');

Route::middleware('auth')->group(function () {
    // Replaced by the Danas briefing in Phase 2.
    Route::inertia('danas', 'Today')->name('today');
});

require __DIR__.'/settings.php';
