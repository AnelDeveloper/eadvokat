<?php

namespace App\Screens;

use App\Enums\DisplayStatus;
use App\Models\Matter;
use App\Models\User;
use App\Presenters\MatterPresenter;
use App\Queries\Agenda;
use App\Queries\AgendaItem;
use App\Support\Format;
use Illuminate\Support\Collection;

/**
 * Danas: the morning briefing. Shared by the web page and /api/v1/today.
 */
final class TodayScreen
{
    /**
     * @return array<string, mixed>
     */
    public function data(User $user): array
    {
        $agenda = new Agenda;
        $today = $agenda->today;
        $tomorrow = $today->addDay();
        // Only the days on screen, plus every proposal still waiting for review.
        $items = $agenda->items(from: $today, to: $today->addDays(Agenda::URGENT_DAYS), withProposed: true);
        $calendar = $items->filter(fn (AgendaItem $i) => $i->onCalendar);

        $rows = fn (Collection $list) => $list
            ->sortBy(fn (AgendaItem $i) => $i->sortKey())
            ->values()
            ->map(fn (AgendaItem $i) => $i->toRow('brief', $today))
            ->all();

        $review = $rows($items->filter(fn (AgendaItem $i) => $i->status === DisplayStatus::Proposed));

        // One row per matter whose cost sheet has items waiting for review.
        Matter::query()->with('costSheet.items')->orderBy('title')->get()
            ->each(function (Matter $matter) use (&$review) {
                $sheet = $matter->costSheet;

                if ($sheet && ! $sheet->isApproved() && $sheet->pendingCount() > 0) {
                    $review[] = [
                        'id' => 'costs:'.$matter->id,
                        'when' => 'KM',
                        'title' => __('agenda.cost_review_title', ['pending' => MatterPresenter::costLine($sheet)]),
                        'sub' => __('agenda.cost_review_sub', ['case' => $matter->case_number]),
                        'status' => DisplayStatus::Proposed->value,
                        'href' => route('matters.costs', $matter, false),
                        'toggleUrl' => null,
                    ];
                }
            });

        $groups = [
            ['key' => 'hearings', 'rows' => $rows($calendar->filter(fn (AgendaItem $i) => $i->eventType === 'hearing' && $i->isOn($today)))],
            ['key' => 'deadlines', 'rows' => $rows($calendar->filter(fn (AgendaItem $i) => $i->kind === AgendaItem::KIND_DEADLINE
                && $i->status !== DisplayStatus::Fulfilled
                && $i->date->betweenIncluded($today, $today->addDays(Agenda::URGENT_DAYS))))],
            ['key' => 'court', 'rows' => $rows($calendar->filter(fn (AgendaItem $i) => $i->kind === AgendaItem::KIND_REMINDER && $i->isOn($today)))],
        ];

        if ($review !== []) {
            $groups[] = ['key' => 'review', 'rows' => $review];
        }

        $groups[] = ['key' => 'tomorrow', 'rows' => $rows($calendar->filter(fn (AgendaItem $i) => $i->isOn($tomorrow)))];

        return [
            'date' => Format::longDate($today),
            'lawyer' => $user->displayName(),
            'groups' => $groups,
            // Dictation needs a speech service; until an office has one, the shortcut is hidden.
            'canDictate' => (bool) $user->office->is_demo,
            // A new office starts with an empty briefing; the page then points to the first step.
            'hasMatters' => Matter::query()->exists(),
        ];
    }
}
