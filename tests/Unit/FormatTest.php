<?php

use App\Support\Format;
use Carbon\CarbonImmutable;

it('formats dates as dd.mm.gggg. with a trailing dot', function () {
    expect(Format::date('2026-11-11'))->toBe('11.11.2026.')
        ->and(Format::date(CarbonImmutable::create(2026, 1, 5)))->toBe('05.01.2026.')
        ->and(Format::date(null))->toBe('')
        ->and(Format::dayMonth('2026-11-23'))->toBe('23.11.');
});

it('formats the long Bosnian date used on Danas', function () {
    expect(Format::longDate('2026-11-11'))->toBe('srijeda, 11. novembar 2026.')
        ->and(Format::weekday('2026-11-13'))->toBe('petak')
        ->and(Format::monthYear(2026, 12))->toBe('Decembar 2026.');
});

it('formats money as 1.234,56 KM', function () {
    expect(Format::moneyKm(1234.56))->toBe('1.234,56 KM')
        ->and(Format::money('480'))->toBe('480,00')
        ->and(Format::money(1800))->toBe('1.800,00')
        ->and(Format::money(null))->toBe('');
});

it('parses dd.mm.gggg. input and rejects invalid dates', function () {
    expect(Format::parseDate('26.11.2026.')?->toDateString())->toBe('2026-11-26')
        ->and(Format::parseDate('1.2.2026')?->toDateString())->toBe('2026-02-01')
        ->and(Format::parseDate('31.02.2026.'))->toBeNull()
        ->and(Format::parseDate('2026-11-26'))->toBeNull();
});

it('uses the Sarajevo time zone and Bosnian locale', function () {
    expect(config('app.timezone'))->toBe('Europe/Sarajevo')
        ->and(app()->getLocale())->toBe('bs')
        ->and(__('validation.required', ['attribute' => 'sud']))->toBe('Polje sud je obavezno.');
});
