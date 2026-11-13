<?php

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CostItemController;
use App\Http\Controllers\CostSheetController;
use App\Http\Controllers\DeadlineController;
use App\Http\Controllers\DemoController;
use App\Http\Controllers\DictationController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\MatterController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\TodayController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Landing')->name('home');
Route::redirect('cijene', '/#cijene')->name('pricing');

Route::middleware('guest')->group(function () {
    Route::get('registracija', [RegisterController::class, 'create'])->name('register');
    Route::post('registracija', [RegisterController::class, 'store'])->middleware('throttle:6,1')->name('register.store');
});

Route::get('demo', [DemoController::class, 'show'])->middleware('throttle:demo')->name('demo');
Route::post('demo/reset', [DemoController::class, 'reset'])->middleware('throttle:demo')->name('demo.reset');
Route::get('demo/napredak', [DemoController::class, 'progress'])->middleware('auth')->name('demo.progress');

Route::middleware('auth')->group(function () {
    Route::get('danas', TodayController::class)->name('today');
    Route::get('kalendar', CalendarController::class)->name('calendar');
    Route::post('podsjetnici/{reminder}/provjereno', [ReminderController::class, 'toggle'])->name('reminders.toggle');

    Route::get('rokovi/{deadline}/prijedlog', [DeadlineController::class, 'review'])->name('deadlines.review');
    Route::post('rokovi/{deadline}/potvrda', [DeadlineController::class, 'confirm'])->name('deadlines.confirm');

    Route::get('predmeti/{matter}/troskovnik', [CostSheetController::class, 'show'])->name('matters.costs');
    Route::patch('predmeti/{matter}/troskovnik', [CostSheetController::class, 'update'])->name('costs.update');
    Route::post('predmeti/{matter}/troskovnik/odobri', [CostSheetController::class, 'approve'])->name('costs.approve');
    Route::get('predmeti/{matter}/troskovnik/pdf', [CostSheetController::class, 'pdf'])->name('costs.pdf');
    Route::patch('troskovi/stavke/{item}', [CostItemController::class, 'update'])->name('cost-items.update');
    Route::post('troskovi/stavke/{item}/potvrda', [CostItemController::class, 'confirm'])->name('cost-items.confirm');
    Route::post('troskovi/stavke/{item}/vrati', [CostItemController::class, 'revert'])->name('cost-items.revert');
    Route::delete('troskovi/stavke/{item}', [CostItemController::class, 'destroy'])->name('cost-items.destroy');

    Route::get('predmeti', [MatterController::class, 'index'])->name('matters.index');
    Route::get('predmeti/novi', [MatterController::class, 'create'])->name('matters.create');
    Route::post('predmeti', [MatterController::class, 'store'])->name('matters.store');
    Route::get('predmeti/{matter}/rokovi/novi', [DeadlineController::class, 'create'])->name('deadlines.create');
    Route::post('predmeti/{matter}/rokovi', [DeadlineController::class, 'store'])->name('deadlines.store');
    Route::get('predmeti/{matter}', [MatterController::class, 'show'])->name('matters.show');
    Route::get('predmeti/{matter}/izmjene', [MatterController::class, 'audit'])->name('matters.audit');

    Route::get('posta', [MailController::class, 'index'])->name('mail.index');
    Route::get('posta/fotografisi', [MailController::class, 'capture'])->name('mail.capture');
    Route::post('posta/fotografisi', [MailController::class, 'store'])->name('mail.store');
    Route::get('posta/podnesak', [SubmissionController::class, 'create'])->name('submissions.create');
    Route::post('posta/podnesak', [SubmissionController::class, 'store'])->name('submissions.store');
    Route::get('posta/pregled/{document}', [MailController::class, 'review'])->name('mail.review');
    Route::post('posta/pregled/{document}', [MailController::class, 'save'])->name('mail.save');

    Route::get('diktat', [DictationController::class, 'create'])->name('dictation.create');
    Route::post('diktat', [DictationController::class, 'store'])->name('dictation.store');
    Route::get('diktat/{document}', [DictationController::class, 'show'])->name('dictation.show');
    Route::post('diktat/{document}/sacuvaj', [DictationController::class, 'save'])->name('dictation.save');
    Route::post('dogadjaji/{event}/potvrda', [EventController::class, 'confirm'])->name('events.confirm');
    Route::post('biljeske/{note}/potvrda', [NoteController::class, 'confirm'])->name('notes.confirm');

    Route::get('dokumenti/{document}', [DocumentController::class, 'show'])->name('documents.show');
    Route::get('dokumenti/{document}/datoteka', [DocumentController::class, 'file'])->name('documents.file');
});

require __DIR__.'/settings.php';
