<?php

namespace App\Http\Controllers;

use App\Actions\Costs\ManageCostSheet;
use App\Demo\DemoProgress;
use App\Enums\ItemStatus;
use App\Enums\Jurisdiction;
use App\Models\CostItem;
use App\Models\CostSheet;
use App\Models\Matter;
use App\Models\User;
use App\Presenters\MatterPresenter;
use App\Support\Clock;
use App\Support\Format;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CostSheetController extends Controller
{
    public function show(Matter $matter, DemoProgress $demo): Response
    {
        Gate::authorize('view', $matter);
        $demo->markSeen($this->office(), 'costs');

        $sheet = $this->sheet($matter)->load('items');

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
            'approvedLine' => $sheet->isApproved() && $sheet->approved_at
                ? __('costs.pdf.sheet_approved', ['name' => $this->approverName($sheet), 'date' => Format::dateTime($sheet->approved_at)])
                : null,
            'jurisdictions' => collect(Jurisdiction::cases())->map(fn (Jurisdiction $j) => [
                'value' => $j->value,
                'label' => $j->shortLabel(),
            ]),
        ]);
    }

    public function update(Request $request, Matter $matter, ManageCostSheet $action): RedirectResponse
    {
        Gate::authorize('update', $matter);
        $validated = $request->validate([
            'jurisdiction' => ['required', Rule::enum(Jurisdiction::class)],
            'tariff_source' => ['nullable', 'string', 'max:500'],
        ], attributes: ['jurisdiction' => 'nadležnost', 'tariff_source' => 'izvor tarife']);

        $action->update($this->sheet($matter), Jurisdiction::from($validated['jurisdiction']), $validated['tariff_source'] ?? null);

        return back();
    }

    public function approve(Matter $matter, ManageCostSheet $action): RedirectResponse
    {
        Gate::authorize('update', $matter);

        $action->approve($this->user(), $this->sheet($matter));
        Inertia::flash('toast', __('costs.approved'));

        return back();
    }

    /**
     * A file for the lawyer only; nothing is sent anywhere.
     */
    public function pdf(Matter $matter): HttpResponse
    {
        Gate::authorize('view', $matter);
        $sheet = $this->sheet($matter)->load('items');

        $pdf = Pdf::loadView('pdf.cost-sheet', [
            'matter' => $matter,
            'sheet' => $sheet,
            'isDemo' => (bool) $this->office()->is_demo,
            'approvedLine' => $sheet->isApproved() && $sheet->approved_at
                ? __('costs.pdf.sheet_approved', ['name' => $this->approverName($sheet), 'date' => Format::dateTime($sheet->approved_at)])
                : __('costs.pdf.sheet_draft'),
            'generated' => __('costs.pdf.generated', ['date' => Format::dateTime(Clock::now())]),
            'total' => Format::money($sheet->items->sum(fn (CostItem $i) => (float) $i->amount)),
        ])->setPaper('a4');

        $name = 'troskovnik-nacrt-'.Str::slug($matter->case_number).'.pdf';

        return $pdf->download($name)->withHeaders(['Cache-Control' => 'private, no-store']);
    }

    private function sheet(Matter $matter): CostSheet
    {
        return $matter->costSheet()->firstOrCreate([], [
            'office_id' => $matter->office_id,
            'jurisdiction' => $matter->jurisdiction,
            'status' => 'draft',
        ]);
    }

    private function approverName(CostSheet $sheet): string
    {
        return (string) User::query()->where('office_id', $sheet->office_id)->whereKey($sheet->approved_by)->value('name');
    }
}
