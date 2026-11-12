<?php

namespace App\Models;

use App\Enums\ItemStatus;
use App\Enums\NoteSource;
use App\Models\Concerns\BelongsToOffice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $office_id
 * @property int $matter_id
 * @property int|null $event_id
 * @property string $body
 * @property string|null $transcript
 * @property NoteSource $source
 * @property int|null $source_document_id
 * @property ItemStatus $status
 * @property int|null $confirmed_by
 * @property Carbon|null $confirmed_at
 * @property Carbon $created_at
 * @property-read Matter $matter
 */
class Note extends Model
{
    use BelongsToOffice;

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'source' => NoteSource::class,
            'status' => ItemStatus::class,
            'confirmed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Matter, $this> */
    public function matter(): BelongsTo
    {
        return $this->belongsTo(Matter::class);
    }

    /** @return BelongsTo<Event, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /** @return BelongsTo<Document, $this> */
    public function sourceDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'source_document_id');
    }
}
