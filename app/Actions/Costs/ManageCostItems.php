<?php

namespace App\Actions\Costs;

use App\Enums\ItemStatus;
use App\Models\CostItem;
use App\Models\User;
use App\Support\Audit;
use App\Support\Clock;
use App\Support\Format;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Per-item review of the draft cost sheet. Amounts are entered by the lawyer
 * (rule 4); editing an amount takes the item back to "proposed".
 */
final class ManageCostItems
{
    public function __construct(private readonly RevertSheetToDraft $revert) {}

    public function updateAmount(CostItem $item, ?string $amount): CostItem
    {
        return DB::transaction(function () use ($item, $amount) {
            $old = ['amount' => $item->amount, 'status' => $item->status->value];

            if ($old['amount'] === $amount) {
                return $item;
            }

            $item->update([
                'amount' => $amount,
                'status' => ItemStatus::Proposed,
                'confirmed_by' => null,
                'confirmed_at' => null,
            ]);

            Audit::record($item->costSheet->matter, 'cost_item.amount_changed', __('audit.cost_amount_changed', [
                'title' => $item->title,
                'old' => $old['amount'] === null ? '—' : Format::moneyKm($old['amount']),
                'new' => $amount === null ? '—' : Format::moneyKm($amount),
            ]), $item, $old, ['amount' => $amount, 'status' => 'proposed']);

            $this->revert->handle($item->costSheet);

            return $item;
        });
    }

    public function confirm(User $user, CostItem $item): CostItem
    {
        if ($item->amount === null) {
            throw ValidationException::withMessages(['amount' => __('costs.amount_required')]);
        }

        return DB::transaction(function () use ($user, $item) {
            $item->update(['status' => ItemStatus::Confirmed, 'confirmed_by' => $user->id, 'confirmed_at' => Clock::now()]);

            Audit::record($item->costSheet->matter, 'cost_item.confirmed', __('audit.cost_confirmed', [
                'title' => $item->title,
                'amount' => Format::moneyKm($item->amount),
            ]), $item, ['status' => 'proposed'], ['status' => 'confirmed', 'amount' => $item->amount]);

            $this->revert->handle($item->costSheet);

            return $item;
        });
    }

    public function revertToReview(CostItem $item): CostItem
    {
        return DB::transaction(function () use ($item) {
            $item->update(['status' => ItemStatus::Proposed, 'confirmed_by' => null, 'confirmed_at' => null]);

            Audit::record($item->costSheet->matter, 'cost_item.reverted', __('audit.cost_reverted', [
                'title' => $item->title,
            ]), $item, ['status' => 'confirmed'], ['status' => 'proposed']);

            $this->revert->handle($item->costSheet);

            return $item;
        });
    }

    public function remove(CostItem $item): void
    {
        DB::transaction(function () use ($item) {
            $sheet = $item->costSheet;

            Audit::record($sheet->matter, 'cost_item.removed', __('audit.cost_removed', ['title' => $item->title]), $item, [
                'title' => $item->title,
                'action_date' => $item->action_date->toDateString(),
                'amount' => $item->amount,
                'status' => $item->status->value,
                'source' => $item->source_label,
            ]);

            $item->delete();
            $this->revert->handle($sheet);
        });
    }
}
