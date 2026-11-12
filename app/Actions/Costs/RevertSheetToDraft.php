<?php

namespace App\Actions\Costs;

use App\Enums\CostSheetStatus;
use App\Models\CostSheet;
use App\Support\Audit;

/**
 * Rule from the README: any change to an amount, item, jurisdiction or tariff
 * source reverts an approved cost sheet to draft. Done only here.
 */
final class RevertSheetToDraft
{
    public function handle(CostSheet $sheet): void
    {
        if ($sheet->status !== CostSheetStatus::Approved) {
            return;
        }

        $sheet->update(['status' => CostSheetStatus::Draft, 'approved_by' => null, 'approved_at' => null]);

        Audit::record($sheet->matter, 'cost_sheet.reverted', __('audit.cost_sheet_reverted'), $sheet, ['status' => 'approved'], ['status' => 'draft']);
    }
}
