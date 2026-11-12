<?php

namespace App\Presenters;

use App\Models\CostSheet;
use App\Models\Matter;

final class MatterPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function header(Matter $matter): array
    {
        $matter->loadMissing('costSheet.items');

        return [
            'id' => $matter->id,
            'caseNumber' => $matter->case_number,
            'title' => $matter->title,
            'meta' => $matter->headerMeta(),
            'costLine' => self::costLine($matter->costSheet),
        ];
    }

    /** "1 stavka čeka pregled" / "sve stavke potvrđene — čeka odobrenje" / "nacrt odobrio advokat" */
    public static function costLine(?CostSheet $sheet): string
    {
        if ($sheet === null) {
            return '—';
        }

        if ($sheet->isApproved()) {
            return __('agenda.cost_approved');
        }

        $pending = $sheet->pendingCount();

        return $pending > 0 ? trans_choice('agenda.cost_pending', $pending) : __('agenda.cost_all_confirmed');
    }
}
