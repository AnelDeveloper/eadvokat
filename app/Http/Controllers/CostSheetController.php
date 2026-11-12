<?php

namespace App\Http\Controllers;

use App\Enums\ItemStatus;
use App\Enums\Jurisdiction;
use App\Models\CostItem;
use App\Models\Matter;
use App\Presenters\MatterPresenter;
use App\Support\Format;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CostSheetController extends Controller
{
    public function show(Matter $matter): Response
    {
        Gate::authorize('view', $matter);

        $sheet = $matter->costSheet()->firstOrCreate([], [
            'office_id' => $matter->office_id,
            'jurisdiction' => $matter->jurisdiction,
            'status' => 'draft',
        ]);
        $sheet->load('items');

        return Inertia::render('matters/Costs', [
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
            'isDemo' => (bool) $this->office()->is_demo,
            'jurisdictions' => collect(Jurisdiction::cases())->map(fn (Jurisdiction $j) => [
                'value' => $j->value,
                'label' => $j->shortLabel(),
            ]),
        ]);
    }
}
