<?php

namespace App\Screens;

use App\Enums\ItemStatus;
use App\Enums\Jurisdiction;
use App\Models\CostItem;
use App\Models\CostSheet;
use App\Models\Matter;
use App\Models\User;
use App\Presenters\MatterPresenter;
use App\Support\Format;

/**
 * Troškovnik tab of a matter.
 */
final class CostsScreen
{
    /**
     * @return array<string, mixed>
     */
    public function data(Matter $matter, bool $isDemo): array
    {
        $sheet = $this->sheet($matter)->load('items');

        return [
            'matter' => MatterPresenter::header($matter),
            'sheet' => [
                'id' => $sheet->id,
                'approved' => $sheet->isApproved(),
                'jurisdiction' => $sheet->jurisdiction->value,
                'tariffSource' => (string) $sheet->tariff_source,
                'pending' => $sheet->pendingCount(),
                'pendingLabel' => $sheet->pendingCount() > 0 ? trans_choice('agenda.cost_pending', $sheet->pendingCount()) : null,
                'total' => Format::money($sheet->items->sum(fn (CostItem $i) => (float) $i->amount)),
                'items' => $sheet->items->map(fn (CostItem $item) => [
                    'id' => $item->id,
                    'title' => $item->title,
                    'date' => Format::date($item->action_date),
                    'source' => $item->source_label,
                    'documentId' => $item->source_document_id,
                    'amount' => $item->amount,
                    'confirmed' => $item->status === ItemStatus::Confirmed,
                ]),
            ],
            'isDemo' => $isDemo,
            'approvedLine' => $sheet->isApproved() && $sheet->approved_at
                ? __('costs.pdf.sheet_approved', ['name' => $this->approverName($sheet), 'date' => Format::dateTime($sheet->approved_at)])
                : null,
            'jurisdictions' => collect(Jurisdiction::cases())->map(fn (Jurisdiction $j) => [
                'value' => $j->value,
                'label' => $j->shortLabel(),
            ]),
        ];
    }

    public function sheet(Matter $matter): CostSheet
    {
        return $matter->costSheet()->firstOrCreate([], [
            'office_id' => $matter->office_id,
            'jurisdiction' => $matter->jurisdiction,
            'status' => 'draft',
        ]);
    }

    public function approverName(CostSheet $sheet): string
    {
        return (string) User::query()->where('office_id', $sheet->office_id)->whereKey($sheet->approved_by)->value('name');
    }
}
