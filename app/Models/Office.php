<?php

namespace App\Models;

use App\Support\Clock;
use Database\Factories\OfficeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property bool $is_demo
 * @property Carbon|null $pinned_today
 * @property Carbon|null $expires_at
 * @property Carbon|null $trial_ends_on
 */
class Office extends Model
{
    /** @use HasFactory<OfficeFactory> */
    use HasFactory;

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_demo' => 'boolean',
            'pinned_today' => 'date',
            'expires_at' => 'datetime',
            'trial_ends_on' => 'date',
        ];
    }

    /**
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /** The trial runs through its last day; afterwards the office keeps its data but cannot work in the app. */
    public function trialExpired(): bool
    {
        return $this->trial_ends_on !== null && Clock::realToday()->gt($this->trial_ends_on);
    }

    /** Days of access left including today, or null when there is no trial. */
    public function trialDaysLeft(): ?int
    {
        if ($this->trial_ends_on === null) {
            return null;
        }

        return max(0, (int) Clock::realToday()->diffInDays($this->trial_ends_on->toImmutable()->startOfDay(), false) + 1);
    }
}
