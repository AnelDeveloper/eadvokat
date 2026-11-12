<?php

use App\Demo\DemoSessions;
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
