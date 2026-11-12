<?php

namespace App\Http\Controllers;

use App\Enums\DisplayStatus;
use App\Queries\Agenda;
use App\Queries\AgendaItem;
use App\Support\Format;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CalendarController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $agenda = new Agenda;
        $today = $agenda->today;

        $month = preg_match('/^\d{4}-\d{2}$/', (string) $request->query('mjesec'))
            ? CarbonImmutable::createFromFormat('!Y-m', (string) $request->query('mjesec'), config('app.timezone'))
            : null;
        $month = ($month ?: $today)->startOfMonth();

        $selectedDay = (int) $request->query('dan', $month->isSameMonth($today) ? $today->day : 1);
        $selectedDay = max(1, min($selectedDay, $month->daysInMonth));
        $selected = $month->setDay($selectedDay);

        $items = $agenda->items()->filter(fn (AgendaItem $i) => $i->onCalendar);

        $days = [];
        for ($day = 1; $day <= $month->daysInMonth; $day++) {
            $date = $month->setDay($day);
            $days[] = [
                'day' => $day,
                'isToday' => $date->isSameDay($today),
                'markers' => $items->filter(fn (AgendaItem $i) => $i->isOn($date))
                    ->sortBy(fn (AgendaItem $i) => $i->sortKey())
                    ->take(3)
                    ->map(fn (AgendaItem $i) => $this->marker($i))
                    ->values(),
            ];
        }

        return Inertia::render('Calendar', [
            'month' => $month->format('Y-m'),
            'monthLabel' => Format::monthYear($month->year, $month->month),
            'prevMonth' => $month->subMonth()->format('Y-m'),
            'nextMonth' => $month->addMonth()->format('Y-m'),
            // Monday-first grid: blank cells before the 1st.
            'offset' => ($month->dayOfWeekIso - 1),
            'days' => $days,
            'selectedDay' => $selectedDay,
            'selectedLabel' => Format::weekday($selected).', '.Format::date($selected),
            'rows' => $items->filter(fn (AgendaItem $i) => $i->isOn($selected))
                ->sortBy(fn (AgendaItem $i) => $i->sortKey())
                ->values()
                ->map(fn (AgendaItem $i) => $i->toRow('day', $today)),
        ]);
    }

    /**
     * ink = hearing or meeting, green = confirmed deadline, red = urgent
     * deadline, hollow amber ring = proposal, grey = reminder or past item.
     */
    private function marker(AgendaItem $item): string
    {
        return match (true) {
            $item->status === DisplayStatus::Proposed => 'proposed',
            $item->kind === AgendaItem::KIND_REMINDER,
            $item->status === DisplayStatus::Recorded,
            $item->status === DisplayStatus::Fulfilled => 'muted',
            $item->kind === AgendaItem::KIND_DEADLINE => $item->status === DisplayStatus::Urgent ? 'urgent' : 'deadline',
            default => 'event',
        };
    }
}
