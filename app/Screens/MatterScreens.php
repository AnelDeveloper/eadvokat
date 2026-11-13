<?php

namespace App\Screens;

use App\Enums\EventType;
use App\Models\AuditLog;
use App\Models\Matter;
use App\Presenters\MatterPresenter;
use App\Queries\Agenda;
use App\Queries\AgendaItem;
use App\Support\Format;
use Illuminate\Support\Collection;

/**
 * Predmeti list, Hronologija and Izmjene.
 */
final class MatterScreens
{
    /**
     * @return array<string, mixed>
     */
    public function index(bool $isDemo): array
    {
        $agenda = new Agenda;
        // Next hearing or confirmed deadline per matter: only today onwards is needed.
        $next = $agenda->items(from: $agenda->today, kinds: [AgendaItem::KIND_EVENT, AgendaItem::KIND_DEADLINE])
            ->filter(fn (AgendaItem $i) => $agenda->isUpcoming($i)
                && ($i->kind === AgendaItem::KIND_DEADLINE || $i->eventType === EventType::Hearing->value)
                && $i->status->value !== 'proposed')
            ->sortBy(fn (AgendaItem $i) => $i->sortKey())
            ->groupBy(fn (AgendaItem $i) => $i->matterId)
            ->map(fn (Collection $group) => $group->first());

        $matters = Matter::query()->with('costSheet.items')->orderBy('title')->get()
            ->map(function (Matter $matter) use ($next) {
                $next = $next->get($matter->id);

                return [
                    'id' => $matter->id,
                    'caseNumber' => $matter->case_number,
                    'title' => $matter->title,
                    'meta' => $matter->listMeta(),
                    'next' => $next ? $next->title.' · '.Format::date($next->date) : '—',
                    'costLine' => MatterPresenter::costLine($matter->costSheet),
                ];
            });

        return [
            'matters' => $matters,
            'isDemo' => $isDemo,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function timeline(Matter $matter): array
    {
        $agenda = new Agenda;
        $items = $agenda->items($matter);
        [$upcoming, $recorded] = $items->partition(fn (AgendaItem $i) => $agenda->isUpcoming($i));

        return [
            'matter' => MatterPresenter::header($matter),
            'upcoming' => $upcoming->sortBy(fn (AgendaItem $i) => $i->sortKey())->values()
                ->map(fn (AgendaItem $i) => $i->toRow('timeline', $agenda->today)),
            'recorded' => $recorded->sortByDesc(fn (AgendaItem $i) => $i->sortKey())->values()
                ->map(fn (AgendaItem $i) => $i->toRow('timeline', $agenda->today)),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function audit(Matter $matter): array
    {
        $entries = $matter->auditLogs()->with('user')->latest('created_at')->latest('id')->get()
            ->map(fn (AuditLog $log) => [
                'id' => $log->id,
                'at' => Format::dateTime($log->created_at),
                'user' => $log->user?->shortName() ?? '—',
                'description' => $log->description,
            ]);

        return [
            'matter' => MatterPresenter::header($matter),
            'entries' => $entries,
        ];
    }
}
