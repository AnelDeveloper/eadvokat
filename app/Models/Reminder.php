<?php

namespace App\Models;

use App\Enums\ReminderKind;
use App\Models\Concerns\BelongsToOffice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * Reminders exist only for confirmed things; proposals never create them.
 *
 * @property int $id
 * @property int $office_id
 * @property int $matter_id
 * @property Carbon $remind_on
 * @property string $text
 * @property string|null $detail
 * @property ReminderKind $kind
 * @property string|null $source_type
 * @property int|null $source_id
 * @property Carbon|null $checked_at
 * @property-read Matter $matter
 */
class Reminder extends Model
{
    use BelongsToOffice;

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kind' => ReminderKind::class,
            'remind_on' => 'date',
            'checked_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Matter, $this> */
    public function matter(): BelongsTo
    {
        return $this->belongsTo(Matter::class);
    }

    /** @return MorphTo<Model, $this> */
    public function source(): MorphTo
    {
        return $this->morphTo();
    }
}
