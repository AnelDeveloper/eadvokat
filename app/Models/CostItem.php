<?php

namespace App\Models;

use App\Enums\ItemStatus;
use App\Models\Concerns\BelongsToOffice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $office_id
 * @property int $cost_sheet_id
 * @property string $title
 * @property Carbon $action_date
 * @property string|null $source_type
 * @property int|null $source_id
 * @property string $source_label
 * @property int|null $source_document_id
 * @property string|null $source_quote
 * @property string|null $amount
 * @property ItemStatus $status
 * @property int|null $confirmed_by
 * @property Carbon|null $confirmed_at
 * @property-read CostSheet $costSheet
 */
class CostItem extends Model
{
    use BelongsToOffice;

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ItemStatus::class,
            'action_date' => 'date',
            'amount' => 'decimal:2',
            'confirmed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<CostSheet, $this> */
    public function costSheet(): BelongsTo
    {
        return $this->belongsTo(CostSheet::class);
    }

    /** @return MorphTo<Model, $this> */
    public function source(): MorphTo
    {
        return $this->morphTo();
    }
}
