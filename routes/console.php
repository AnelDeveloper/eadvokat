<?php

use App\Demo\DemoSessions;
use App\Models\User;
use App\Support\Clock;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('demo:prune', function (DemoSessions $sessions) {
    $this->info('Obrisano demo kancelarija: '.$sessions->prune());
})->purpose('Delete live-demo offices older than 24 hours');

Schedule::command('demo:prune')->hourly();

Artisan::command('office:trial {email : E-mail korisnika kancelarije} {days=10 : Broj dana pristupa od danas} {--unlimited : Ukloni probni rok (plaćen pristup)}', function (string $email, string $days) {
    $user = User::query()->where('email', mb_strtolower($email))->first();

    if ($user === null) {
        $this->error("Korisnik {$email} ne postoji.");

        return 1;
    }

    $office = $user->office;
    $office->update([
        'trial_ends_on' => $this->option('unlimited') ? null : Clock::realToday()->addDays(max(1, (int) $days) - 1)->toDateString(),
    ]);

    $this->info($office->trial_ends_on === null
        ? "{$office->name}: pristup bez ograničenja."
        : "{$office->name}: pristup do {$office->trial_ends_on->format('d.m.Y.')}");

    return 0;
})->purpose('Set or extend an office trial (or remove it with --unlimited)');
