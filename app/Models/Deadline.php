<?php

namespace App\Models;

use App\Enums\DeadlineStatus;
use App\Enums\Jurisdiction;
use App\Models\Concerns\BelongsToOffice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $office_id
 * @property int $matter_id
 * @property string $title
 * @property string $rule_text
 * @property int|null $days
 * @property string $basis
 * @property Jurisdiction $jurisdiction
 * @property int|null $source_document_id
 * @property int|null $source_mail_entry_id
 * @property int|null $source_note_id
 * @property string|null $source_quote
 * @property string|null $source_highlight
 * @property Carbon $start_date
 * @property Carbon $due_date
 * @property bool $due_date_manually_changed
 * @property bool $remind_3_days
 * @property bool $remind_1_day
 * @property DeadlineStatus $status
 * @property bool $rule_ack
 * @property bool $start_ack
 * @property bool $due_ack
 * @property int|null $confirmed_by
 * @property Carbon|null $confirmed_at
 * @property int|null $fulfilled_by_mail_entry_id
 * @property Carbon|null $fulfilled_at
 * @property-read Matter $matter
 * @property-read Document|null $sourceDocument
 * @property-read MailEntry|null $sourceMailEntry
 * @property-read MailEntry|null $fulfilledBy
 */
class Deadline extends Model
{
    use BelongsToOffice;

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => DeadlineStatus::class,
            'jurisdiction' => Jurisdiction::class,
            'start_date' => 'date',
            'due_date' => 'date',
            'due_date_manually_changed' => 'boolean',
            'remind_3_days' => 'boolean',
            'remind_1_day' => 'boolean',
            'rule_ack' => 'boolean',
            'start_ack' => 'boolean',
            'due_ack' => 'boolean',
            'confirmed_at' => 'datetime',
            'fulfilled_at' => 'datetime',
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

    /** @return BelongsTo<MailEntry, $this> */
    public function sourceMailEntry(): BelongsTo
    {
        return $this->belongsTo(MailEntry::class, 'source_mail_entry_id');
    }

    /** @return BelongsTo<MailEntry, $this> */
    public function fulfilledBy(): BelongsTo
    {
        return $this->belongsTo(MailEntry::class, 'fulfilled_by_mail_entry_id');
    }

    /** @return MorphMany<Reminder, $this> */
    public function reminders(): MorphMany
    {
        return $this->morphMany(Reminder::class, 'source');
    }

    public function isProposed(): bool
    {
        return $this->status === DeadlineStatus::Proposed;
    }
}
