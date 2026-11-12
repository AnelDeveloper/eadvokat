<?php

namespace App\Services;

use App\Enums\MailDirection;
use Illuminate\Support\Facades\DB;

/**
 * Register numbers U-YYYY/NNN (incoming) and I-YYYY/NNN (outgoing),
 * sequential per office, direction and year.
 */
final class RegisterNumbers
{
    public static function format(MailDirection $direction, int $year, int $sequence): string
    {
        return sprintf('%s-%d/%03d', $direction->prefix(), $year, $sequence);
    }

    /** The number the next entry would get; nothing is reserved. */
    public function peek(int $officeId, MailDirection $direction, int $year): string
    {
        $last = (int) DB::table('register_counters')
            ->where(['office_id' => $officeId, 'direction' => $direction->value, 'year' => $year])
            ->value('last_number');

        return self::format($direction, $year, $last + 1);
    }

    /**
     * Allocates the next number. Must run inside the saving transaction so a
     * failed save does not leave a gap.
     *
     * @return array{0: int, 1: string}
     */
    public function next(int $officeId, MailDirection $direction, int $year): array
    {
        $key = ['office_id' => $officeId, 'direction' => $direction->value, 'year' => $year];

        DB::table('register_counters')->insertOrIgnore([...$key, 'last_number' => 0]);

        $sequence = (int) DB::table('register_counters')->where($key)->lockForUpdate()->value('last_number') + 1;
        DB::table('register_counters')->where($key)->update(['last_number' => $sequence]);

        return [$sequence, self::format($direction, $year, $sequence)];
    }
}
