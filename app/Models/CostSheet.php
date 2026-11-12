<?php

namespace App\Models;

use App\Enums\CostSheetStatus;
use App\Enums\ItemStatus;
use App\Enums\Jurisdiction;
use App\Models\Concerns\BelongsToOffice;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $office_id
 * @property int $matter_id
 * @property Jurisdiction $jurisdiction
 * @property string|null $tariff_source
 * @property CostSheetStatus $status
 * @property int|null $approved_by
 * @property Carbon|null $approved_at
 * @property-read Matter $matter
 * @property-read Collection<int, CostItem> $items
 */
class CostSheet extends Model
{
    use BelongsToOffice;

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'jurisdiction' => Jurisdiction::class,
            'status' => CostSheetStatus::class,
            'approved_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Matter, $this> */
    public function matter(): BelongsTo
    {
        return $this->belongsTo(Matter::class);
    }

    /** @return HasMany<CostItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(CostItem::class)->orderBy('action_date')->orderBy('id');
    }

    public function pendingCount(): int
    {
        return $this->items->where('status', ItemStatus::Proposed)->count();
    }

    public function isApproved(): bool
    {
        return $this->status === CostSheetStatus::Approved;
    }
}
