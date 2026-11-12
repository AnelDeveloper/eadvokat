<?php

namespace App\Models;

use App\Enums\DeliveryMethod;
use App\Enums\ItemStatus;
use App\Enums\MailDirection;
use App\Models\Concerns\BelongsToOffice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $office_id
 * @property int $matter_id
 * @property MailDirection $direction
 * @property int $register_year
 * @property int $register_sequence
 * @property string $register_number
 * @property Carbon $received_or_sent_on
 * @property Carbon|null $document_date
 * @property string $court
 * @property string $case_number
 * @property string $doc_type
 * @property string|null $summary
 * @property string|null $note
 * @property DeliveryMethod|null $delivery_method
 * @property int|null $document_id
 * @property string|null $source_quote
 * @property int|null $proof_document_id
 * @property string|null $proof_note
 * @property ItemStatus $status
 * @property int|null $confirmed_by
 * @property Carbon|null $confirmed_at
 * @property-read Matter $matter
 * @property-read Document|null $document
 * @property-read Document|null $proofDocument
 */
class MailEntry extends Model
{
    use BelongsToOffice;

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'direction' => MailDirection::class,
            'delivery_method' => DeliveryMethod::class,
            'status' => ItemStatus::class,
            'received_or_sent_on' => 'date',
            'document_date' => 'date',
            'confirmed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Matter, $this> */
    public function matter(): BelongsTo
    {
        return $this->belongsTo(Matter::class);
    }

    /** @return BelongsTo<Document, $this> */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /** @return BelongsTo<Document, $this> */
    public function proofDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'proof_document_id');
    }

    /**
     * Deadlines proposed from this entry.
     *
     * @return HasMany<Deadline, $this>
     */
    public function deadlines(): HasMany
    {
        return $this->hasMany(Deadline::class, 'source_mail_entry_id');
    }
}
