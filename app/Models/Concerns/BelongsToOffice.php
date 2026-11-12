<?php

namespace App\Models\Concerns;

use App\Models\Office;
use App\Support\Clock;
use App\Support\CurrentOffice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Rule 10: every query is scoped to the current user's office.
 *
 * @mixin Model
 */
trait BelongsToOffice
{
    public static function bootBelongsToOffice(): void
    {
        static::addGlobalScope('office', function (Builder $builder): void {
            $officeId = CurrentOffice::id();

            if ($officeId !== null) {
                $builder->where($builder->getModel()->qualifyColumn('office_id'), $officeId);
            }
        });

        static::creating(function (Model $model): void {
            if ($model->getAttribute('office_id') === null) {
                $model->setAttribute('office_id', CurrentOffice::id());
            }
        });
    }

    /**
     * Timestamps follow the domain clock, so a pinned demo date stays consistent.
     */
    public function freshTimestamp(): Carbon
    {
        return Carbon::instance(Clock::now());
    }

    /**
     * @return BelongsTo<Office, $this>
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }
}
