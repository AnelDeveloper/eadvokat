<?php

namespace App\Http\Controllers;

use App\Enums\EventType;
use App\Models\AuditLog;
use App\Models\Matter;
use App\Presenters\MatterPresenter;
use App\Queries\Agenda;
use App\Queries\AgendaItem;
use App\Support\Format;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class MatterController extends Controller
{
    public function index(): Response
    {
        $agenda = new Agenda;
        $items = $agenda->items();

        $matters = Matter::query()->with('costSheet.items')->orderBy('title')->get()
            ->map(function (Matter $matter) use ($agenda, $items) {
                $next = $items
                    ->filter(fn (AgendaItem $i) => $i->matterId === $matter->id
                        && $agenda->isUpcoming($i)
                        && ($i->kind === AgendaItem::KIND_DEADLINE || $i->eventType === EventType::Hearing->value)
                        && $i->status->value !== 'proposed')
                    ->sortBy(fn (AgendaItem $i) => $i->sortKey())
                    ->first();

                return [
                    'id' => $matter->id,
                    'caseNumber' => $matter->case_number,
                    'title' => $matter->title,
                    'meta' => $matter->listMeta(),
                    'next' => $next ? $next->title.' · '.Format::date($next->date) : '—',
                    'costLine' => MatterPresenter::costLine($matter->costSheet),
                ];
            });

        return Inertia::render('matters/Index', [
            'matters' => $matters,
            'isDemo' => (bool) $this->office()->is_demo,
        ]);
    }

    public function show(Matter $matter): Response
    {
        Gate::authorize('view', $matter);

        $agenda = new Agenda;
        $items = $agenda->items($matter);
        [$upcoming, $recorded] = $items->partition(fn (AgendaItem $i) => $agenda->isUpcoming($i));

        return Inertia::render('matters/Timeline', [
            'matter' => MatterPresenter::header($matter),
            'upcoming' => $upcoming->sortBy(fn (AgendaItem $i) => $i->sortKey())->values()
                ->map(fn (AgendaItem $i) => $i->toRow('timeline', $agenda->today)),
            'recorded' => $recorded->sortByDesc(fn (AgendaItem $i) => $i->sortKey())->values()
                ->map(fn (AgendaItem $i) => $i->toRow('timeline', $agenda->today)),
        ]);
    }

    public function audit(Matter $matter): Response
    {
        Gate::authorize('view', $matter);

        $entries = $matter->auditLogs()->with('user')->latest('created_at')->latest('id')->get()
            ->map(fn (AuditLog $log) => [
                'id' => $log->id,
                'at' => Format::dateTime($log->created_at),
                'user' => $log->user?->shortName() ?? '—',
                'description' => $log->description,
            ]);

        return Inertia::render('matters/Audit', [
            'matter' => MatterPresenter::header($matter),
            'entries' => $entries,
        ]);
    }
}
