<?php

namespace App\Actions\Costs;

use App\Enums\CostSheetStatus;
use App\Enums\ItemStatus;
use App\Enums\Jurisdiction;
use App\Models\CostSheet;
use App\Models\User;
use App\Support\Audit;
use App\Support\Clock;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ManageCostSheet
{
    public function __construct(private readonly RevertSheetToDraft $revert) {}

    public function update(CostSheet $sheet, Jurisdiction $jurisdiction, ?string $tariffSource): CostSheet
    {
        return DB::transaction(function () use ($sheet, $jurisdiction, $tariffSource) {
            $matter = $sheet->matter;
            $tariffSource = $tariffSource === null || trim($tariffSource) === '' ? null : trim($tariffSource);
            $changed = false;

            if ($sheet->jurisdiction !== $jurisdiction) {
                Audit::record($matter, 'cost_sheet.jurisdiction_changed', __('audit.jurisdiction_changed', [
                    'old' => $sheet->jurisdiction->label(),
                    'new' => $jurisdiction->label(),
                ]), $sheet, ['jurisdiction' => $sheet->jurisdiction->value], ['jurisdiction' => $jurisdiction->value]);
                $changed = true;
            }

            if ($sheet->tariff_source !== $tariffSource) {
                Audit::record($matter, 'cost_sheet.tariff_changed', __('audit.tariff_changed', [
                    'source' => $tariffSource ?? '—',
                ]), $sheet, ['tariff_source' => $sheet->tariff_source], ['tariff_source' => $tariffSource]);
                $changed = true;
            }

            if ($changed) {
                $sheet->update(['jurisdiction' => $jurisdiction, 'tariff_source' => $tariffSource]);
                $this->revert->handle($sheet);
            }

            return $sheet;
        });
    }

    public function approve(User $user, CostSheet $sheet): CostSheet
    {
        $sheet->load('items');

        if ($sheet->items->isEmpty() || $sheet->items->contains(fn ($item) => $item->status !== ItemStatus::Confirmed)) {
            throw ValidationException::withMessages(['items' => __('costs.confirm_all_first')]);
        }

        if (trim((string) $sheet->tariff_source) === '') {
            throw ValidationException::withMessages(['tariff_source' => __('costs.tariff_required')]);
        }

        return DB::transaction(function () use ($user, $sheet) {
            $sheet->update(['status' => CostSheetStatus::Approved, 'approved_by' => $user->id, 'approved_at' => Clock::now()]);

            Audit::record($sheet->matter, 'cost_sheet.approved', __('audit.cost_sheet_approved', [
                'jurisdiction' => $sheet->jurisdiction->label(),
                'source' => $sheet->tariff_source,
            ]), $sheet, ['status' => 'draft'], [
                'status' => 'approved',
                'jurisdiction' => $sheet->jurisdiction->value,
                'tariff_source' => $sheet->tariff_source,
                'total' => $sheet->items->sum(fn ($i) => (float) $i->amount),
            ]);

            return $sheet;
        });
    }
}
