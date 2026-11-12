<?php

namespace App\Support;

use App\Models\Office;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * The office every query is scoped to: the signed-in user's office.
 */
final class CurrentOffice
{
    private static ?int $override = null;

    /** @var array<int, Office|null> */
    private static array $cache = [];

    public static function id(): ?int
    {
        if (self::$override !== null) {
            return self::$override;
        }

        /** @var User|null $user */
        $user = Auth::user();

        return $user?->office_id;
    }

    public static function get(): ?Office
    {
        $id = self::id();

        if ($id === null) {
            return null;
        }

        return self::$cache[$id] ??= Office::query()->find($id);
    }

    public static function flush(): void
    {
        self::$cache = [];
    }

    /**
     * Runs a callback as a given office (console commands, seeders, demo setup).
     *
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public static function as(int $officeId, callable $callback): mixed
    {
        $previous = self::$override;
        self::$override = $officeId;

        try {
            return $callback();
        } finally {
            self::$override = $previous;
        }
    }
}
