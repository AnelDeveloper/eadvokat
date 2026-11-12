<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use DateTimeInterface;

/**
 * Bosnian display formats: dates as dd.mm.gggg., money as 1.234,56 KM.
 */
final class Format
{
    public const WEEKDAYS = ['nedjelja', 'ponedjeljak', 'utorak', 'srijeda', 'četvrtak', 'petak', 'subota'];

    public const WEEKDAYS_SHORT = ['ned', 'pon', 'uto', 'sri', 'čet', 'pet', 'sub'];

    public const MONTHS = ['januar', 'februar', 'mart', 'april', 'maj', 'juni', 'juli', 'august', 'septembar', 'oktobar', 'novembar', 'decembar'];

    public static function date(DateTimeInterface|string|null $value): string
    {
        $date = self::toDate($value);

        return $date ? $date->format('d.m.Y').'.' : '';
    }

    public static function dayMonth(DateTimeInterface|string|null $value): string
    {
        $date = self::toDate($value);

        return $date ? $date->format('d.m.') : '';
    }

    public static function dateTime(DateTimeInterface|string|null $value): string
    {
        $date = self::toDate($value);

        return $date ? $date->format('d.m.Y.').' '.$date->format('H:i') : '';
    }

    public static function weekday(DateTimeInterface|string|null $value): string
    {
        $date = self::toDate($value);

        return $date ? self::WEEKDAYS[(int) $date->format('w')] : '';
    }

    /**
     * "srijeda, 11. novembar 2026."
     */
    public static function longDate(DateTimeInterface|string|null $value): string
    {
        $date = self::toDate($value);

        if (! $date) {
            return '';
        }

        return self::weekday($date).', '.$date->format('j').'. '.self::MONTHS[(int) $date->format('n') - 1].' '.$date->format('Y').'.';
    }

    /**
     * "Novembar 2026."
     */
    public static function monthYear(int $year, int $month): string
    {
        return ucfirst(self::MONTHS[$month - 1]).' '.$year.'.';
    }

    public static function money(float|int|string|null $amount): string
    {
        if ($amount === null || $amount === '') {
            return '';
        }

        return number_format((float) $amount, 2, ',', '.');
    }

    public static function moneyKm(float|int|string|null $amount): string
    {
        $formatted = self::money($amount);

        return $formatted === '' ? '' : $formatted.' KM';
    }

    /**
     * Parses "dd.mm.gggg." (trailing dot optional) into a date.
     */
    public static function parseDate(?string $value): ?CarbonImmutable
    {
        if ($value === null || ! preg_match('/^\s*(\d{1,2})\.\s*(\d{1,2})\.\s*(\d{4})\.?\s*$/', $value, $m)) {
            return null;
        }

        if (! checkdate((int) $m[2], (int) $m[1], (int) $m[3])) {
            return null;
        }

        return CarbonImmutable::create((int) $m[3], (int) $m[2], (int) $m[1])->startOfDay();
    }

    private static function toDate(DateTimeInterface|string|null $value): ?CarbonImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $value instanceof DateTimeInterface
            ? CarbonImmutable::instance($value)->setTimezone(config('app.timezone'))
            : CarbonImmutable::parse($value, config('app.timezone'));
    }
}
