<?php

namespace App\Support;

use Carbon\CarbonImmutable;

/**
 * "Now" and "today" for domain logic, in Europe/Sarajevo.
 *
 * A live-demo office pins its own date; locally DEMO_TODAY pins the date for
 * the seeded scenario. The time of day always stays real.
 */
final class Clock
{
    public static function now(): CarbonImmutable
    {
        $now = CarbonImmutable::now(config('app.timezone'));
        $pinned = self::pinnedDate();

        return $pinned === null
            ? $now
            : $pinned->setTime((int) $now->format('H'), (int) $now->format('i'), (int) $now->format('s'));
    }

    public static function today(): CarbonImmutable
    {
        return self::now()->startOfDay();
    }

    /** The real calendar date, never pinned: used for access periods, not for case data. */
    public static function realToday(): CarbonImmutable
    {
        return CarbonImmutable::now(config('app.timezone'))->startOfDay();
    }

    private static function pinnedDate(): ?CarbonImmutable
    {
        $office = CurrentOffice::get();

        if ($office?->pinned_today !== null) {
            return CarbonImmutable::parse($office->pinned_today->toDateString(), config('app.timezone'));
        }

        $demoToday = config('rokovnik.demo_today');

        if (is_string($demoToday) && $demoToday !== '' && app()->environment('local')) {
            return CarbonImmutable::parse($demoToday, config('app.timezone'));
        }

        return null;
    }
}
