<?php

namespace App\Models;

use App\Enums\EventType;
use App\Enums\ItemStatus;
use App\Models\Concerns\BelongsToOffice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $office_id
 * @property int $matter_id
 * @property EventType $type
 * @property string $title
 * @property string|null $description
 * @property Carbon $starts_at
 * @property string|null $location
 * @property string|null $venue
 * @property array<string, array<int, string>>|null $uncertain
 * @property string|null $outcome
 * @property string|null $outcome_detail
 * @property int|null $source_document_id
 * @property string|null $source_quote
 * @property ItemStatus $status
 * @property int|null $confirmed_by
 * @property Carbon|null $confirmed_at
 * @property-read Matter $matter
 */
class Event extends Model
{
    use BelongsToOffice;

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => EventType::class,
            'status' => ItemStatus::class,
            'starts_at' => 'datetime',
            'uncertain' => 'array',
            'confirmed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Matter, $this> */
    public function matter(): BelongsTo
    {
        return $this->belongsTo(Matter::class);
    }

    /** @return BelongsTo<Document, $this> */
    public function sourceDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'source_document_id');
    }

    /** "Sudnica 12 · Općinski sud u Sarajevu" */
    public function place(): string
    {
        return collect([$this->location, $this->venue])->filter()->implode(' · ');
    }
}
