<?php

use App\Http\Controllers\Api\V1\ActionController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ScreenController;
use Illuminate\Support\Facades\Route;

/*
 * JSON API for the mobile app (Sanctum bearer tokens). Same domain actions and
 * screen data as the web app; every query is scoped to the user's office.
 */
Route::prefix('v1')->group(function () {
    Route::post('auth/token', [AuthController::class, 'store'])->middleware('throttle:login-api');
    Route::post('auth/register', [AuthController::class, 'register'])->middleware('throttle:6,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'show']);
        Route::delete('auth/token', [AuthController::class, 'destroy']);
    });

    // Office data: closed once the office's trial has ended.
    Route::middleware(['auth:sanctum', 'trial'])->group(function () {

        Route::get('today', [ScreenController::class, 'today']);
        Route::get('calendar', [ScreenController::class, 'calendar']);
        Route::get('matters', [ScreenController::class, 'matters']);
        Route::get('matters/create', [ScreenController::class, 'matterCreate']);
        Route::post('matters', [ActionController::class, 'createMatter']);
        Route::get('matters/{matter}', [ScreenController::class, 'matter']);
        Route::get('matters/{matter}/deadlines/create', [ScreenController::class, 'deadlineCreate']);
        Route::post('matters/{matter}/deadlines', [ActionController::class, 'proposeDeadline']);
        Route::get('matters/{matter}/audit', [ScreenController::class, 'audit']);
        Route::get('matters/{matter}/costs', [ScreenController::class, 'costs']);
        Route::get('mail', [ScreenController::class, 'mail']);
        Route::get('mail/review/{document}', [ScreenController::class, 'mailReview']);
        Route::get('deadlines/{deadline}', [ScreenController::class, 'deadline']);
        Route::get('dictation', [ScreenController::class, 'dictation']);
        Route::get('dictation/{document}', [ScreenController::class, 'dictationResult']);
        Route::get('submissions/create', [ScreenController::class, 'submission']);
        Route::get('documents/{document}', [ScreenController::class, 'document']);
        Route::get('documents/{document}/file', [ActionController::class, 'documentFile']);

        Route::post('mail/capture', [ActionController::class, 'capture']);
        Route::post('mail/review/{document}', [ActionController::class, 'saveMail']);
        Route::post('deadlines/{deadline}/confirm', [ActionController::class, 'confirmDeadline']);
        Route::post('reminders/{reminder}/toggle', [ActionController::class, 'toggleReminder']);
        Route::post('dictation', [ActionController::class, 'dictate']);
        Route::post('dictation/{document}/save', [ActionController::class, 'saveDictation']);
        Route::post('events/{event}/confirm', [ActionController::class, 'confirmEvent']);
        Route::post('notes/{note}/confirm', [ActionController::class, 'confirmNote']);
        Route::post('submissions', [ActionController::class, 'submit']);
        Route::patch('matters/{matter}/costs', [ActionController::class, 'updateCostSheet']);
        Route::post('matters/{matter}/costs/approve', [ActionController::class, 'approveCostSheet']);
        Route::get('matters/{matter}/costs/pdf', [ActionController::class, 'costPdf']);
        Route::patch('cost-items/{item}', [ActionController::class, 'updateCostItem']);
        Route::post('cost-items/{item}/confirm', [ActionController::class, 'confirmCostItem']);
        Route::post('cost-items/{item}/revert', [ActionController::class, 'revertCostItem']);
        Route::delete('cost-items/{item}', [ActionController::class, 'removeCostItem']);
    });
});
