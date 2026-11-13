<?php

namespace App\Queries;

use App\Enums\DeadlineStatus;
use App\Enums\DisplayStatus;
use App\Enums\EventType;
use App\Enums\ItemStatus;
use App\Enums\MailDirection;
use App\Enums\NoteSource;
use App\Models\Deadline;
use App\Models\Event;
use App\Models\MailEntry;
use App\Models\Matter;
use App\Models\Note;
use App\Models\Reminder;
use App\Support\Clock;
use App\Support\Format;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

/**
 * Collects everything that happened or will happen in a matter (or in the
 * whole office) as display rows, and decides how each one is labelled.
 */
final class Agenda
{
    /** Confirmed deadlines due within this many days are shown as urgent. */
    public const URGENT_DAYS = 3;

    public readonly CarbonImmutable $today;

    public function __construct(?CarbonImmutable $today = null)
    {
        $this->today = $today ?? Clock::today();
    }

    /**
     * Agenda rows, optionally limited to a date window so screens that only
     * show a few days never load the office's whole history.
     *
     * @param  CarbonImmutable|null  $from  first day (inclusive), office time zone
     * @param  CarbonImmutable|null  $to  last day (inclusive), office time zone
     * @param  bool  $withProposed  also include proposals outside the window
     * @param  list<string>|null  $kinds  AgendaItem::KIND_* to load; null = all
     * @return Collection<int, AgendaItem>
     */
    public function items(
        ?Matter $matter = null,
        ?CarbonImmutable $from = null,
        ?CarbonImmutable $to = null,
        bool $withProposed = false,
        ?array $kinds = null,
    ): Collection {
        $wants = fn (string $kind): bool => $kinds === null || in_array($kind, $kinds, true);

        $items = collect();

        if ($wants(AgendaItem::KIND_EVENT)) {
            $items = $items->concat($this->window(Event::query(), $matter, $from, $to, $withProposed, column: 'starts_at', timestamp: true, proposed: ItemStatus::Proposed->value)
                ->get()->map(fn (Event $e) => $this->event($e)));
        }
        if ($wants(AgendaItem::KIND_DEADLINE)) {
            $items = $items->concat($this->window(Deadline::query()->with('fulfilledBy'), $matter, $from, $to, $withProposed, column: 'due_date', timestamp: false, proposed: DeadlineStatus::Proposed->value)
                ->get()->map(fn (Deadline $d) => $this->deadline($d)));
        }
        if ($wants(AgendaItem::KIND_REMINDER)) {
            $items = $items->concat($this->window(Reminder::query(), $matter, $from, $to, $withProposed, column: 'remind_on', timestamp: false, proposed: null)
                ->get()->map(fn (Reminder $r) => $this->reminder($r)));
        }
        if ($wants(AgendaItem::KIND_MAIL)) {
            $items = $items->concat($this->window(MailEntry::query(), $matter, $from, $to, $withProposed, column: 'received_or_sent_on', timestamp: false, proposed: ItemStatus::Proposed->value)
                ->get()->map(fn (MailEntry $m) => $this->mail($m)));
        }
        if ($wants(AgendaItem::KIND_NOTE)) {
            $items = $items->concat($this->window(Note::query(), $matter, $from, $to, $withProposed, column: 'created_at', timestamp: true, proposed: ItemStatus::Proposed->value)
                ->get()->map(fn (Note $n) => $this->note($n)));
        }

        return $items->values();
    }

    /**
     * Upcoming = today or later, still live, and something you act on.
     */
    public function isUpcoming(AgendaItem $item): bool
    {
        $actionable = in_array($item->kind, [AgendaItem::KIND_DEADLINE, AgendaItem::KIND_REMINDER], true)
            || ($item->kind === AgendaItem::KIND_EVENT && $item->eventType !== EventType::CourtVisit->value);

        return $item->date->greaterThanOrEqualTo($this->today)
            && $actionable
            && in_array($item->status, [DisplayStatus::Confirmed, DisplayStatus::Proposed, DisplayStatus::Urgent], true);
    }

    public function event(Event $event): AgendaItem
    {
        $startsAt = CarbonImmutable::instance($event->starts_at)->setTimezone(config('app.timezone'));
        $date = $startsAt->startOfDay();

        $status = match (true) {
            $event->status === ItemStatus::Proposed => DisplayStatus::Proposed,
            $event->outcome !== null, $date->lessThan($this->today) => DisplayStatus::Recorded,
            default => DisplayStatus::Confirmed,
        };

        return new AgendaItem(
            key: 'event:'.$event->id,
            kind: AgendaItem::KIND_EVENT,
            matterId: $event->matter_id,
            date: $date,
            time: $startsAt->format('H:i'),
            type: $event->type->label(),
            title: $event->outcome ?? $event->title,
            sub: $event->outcome_detail ?? $event->description ?? $event->place(),
            status: $status,
            onCalendar: true,
            eventType: $event->type->value,
            href: $status === DisplayStatus::Proposed && $event->source_document_id
                ? $this->route('dictation.show', $event->source_document_id)
                : $this->route('matters.show', $event->matter_id),
            documentId: null,
        );
    }

    public function deadline(Deadline $deadline): AgendaItem
    {
        $due = CarbonImmutable::parse($deadline->due_date->toDateString(), config('app.timezone'));
        $daysLeft = (int) $this->today->diffInDays($due, false);

        $status = match (true) {
            $deadline->status === DeadlineStatus::Fulfilled => DisplayStatus::Fulfilled,
            $deadline->status === DeadlineStatus::Proposed => DisplayStatus::Proposed,
            $daysLeft >= 0 && $daysLeft <= self::URGENT_DAYS => DisplayStatus::Urgent,
            default => DisplayStatus::Confirmed,
        };

        if ($status === DisplayStatus::Fulfilled && $deadline->fulfilledBy) {
            $sub = __('agenda.fulfilled_by', [
                'number' => $deadline->fulfilledBy->register_number,
                'date' => Format::date($deadline->fulfilledBy->received_or_sent_on),
            ]);
        } else {
            $parts = [Format::weekday($due)];

            if ($status === DisplayStatus::Urgent && $daysLeft > 0) {
                $parts[] = trans_choice('agenda.in_days', $daysLeft);
            }

            $parts[] = $deadline->basis.' '.Format::date($deadline->start_date);
            $sub = implode(' · ', $parts);
        }

        return new AgendaItem(
            key: 'deadline:'.$deadline->id,
            kind: AgendaItem::KIND_DEADLINE,
            matterId: $deadline->matter_id,
            date: $due,
            time: null,
            type: __('agenda.type.deadline'),
            title: $deadline->title,
            sub: $sub,
            status: $status,
            onCalendar: true,
            href: $status === DisplayStatus::Proposed
                ? $this->route('deadlines.review', $deadline->id)
                : $this->route('matters.show', $deadline->matter_id),
            documentId: $deadline->source_document_id,
        );
    }

    public function reminder(Reminder $reminder): AgendaItem
    {
        $date = CarbonImmutable::parse($reminder->remind_on->toDateString(), config('app.timezone'));
        $isToday = $date->isSameDay($this->today);
        $checked = $reminder->checked_at !== null;

        return new AgendaItem(
            key: 'reminder:'.$reminder->id,
            kind: AgendaItem::KIND_REMINDER,
            matterId: $reminder->matter_id,
            date: $date,
            time: null,
            type: __('agenda.type.reminder'),
            title: $reminder->text,
            sub: $isToday
                ? __($checked ? 'agenda.reminder_checked' : 'agenda.reminder_tap')
                : ($reminder->detail ?? ''),
            status: $checked ? DisplayStatus::Recorded : DisplayStatus::Confirmed,
            onCalendar: true,
            href: $this->route('matters.show', $reminder->matter_id),
            toggleUrl: $isToday ? $this->route('reminders.toggle', $reminder->id) : null,
        );
    }

    public function mail(MailEntry $entry): AgendaItem
    {
        $date = CarbonImmutable::parse($entry->received_or_sent_on->toDateString(), config('app.timezone'));
        $title = $entry->summary ?? __(
            $entry->direction === MailDirection::In ? 'agenda.mail_in_title' : 'agenda.mail_out_title',
            ['type' => $entry->doc_type],
        );

        $status = match (true) {
            $entry->status === ItemStatus::Proposed => DisplayStatus::Proposed,
            $date->lessThan($this->today) => DisplayStatus::Recorded,
            default => DisplayStatus::Confirmed,
        };

        $confirmedAt = $entry->confirmed_at
            ? CarbonImmutable::instance($entry->confirmed_at)->setTimezone(config('app.timezone'))
            : null;

        return new AgendaItem(
            key: 'mail:'.$entry->id,
            kind: AgendaItem::KIND_MAIL,
            matterId: $entry->matter_id,
            date: $date,
            // Older register entries are shown by date only, as in the prototype.
            time: $status !== DisplayStatus::Recorded && $confirmedAt && $confirmedAt->isSameDay($date) ? $confirmedAt->format('H:i') : null,
            type: $entry->direction->label(),
            title: $title,
            sub: collect([$entry->register_number, $entry->note])->filter()->implode(' · '),
            status: $status,
            onCalendar: false,
            href: $this->route('matters.show', $entry->matter_id),
            documentId: $entry->document_id,
        );
    }

    public function note(Note $note): AgendaItem
    {
        $createdAt = CarbonImmutable::instance($note->created_at)->setTimezone(config('app.timezone'));
        $proposed = $note->status === ItemStatus::Proposed;

        return new AgendaItem(
            key: 'note:'.$note->id,
            kind: AgendaItem::KIND_NOTE,
            matterId: $note->matter_id,
            date: $createdAt->startOfDay(),
            time: $createdAt->format('H:i'),
            type: __('agenda.type.note'),
            title: __($note->source === NoteSource::Voice ? 'agenda.voice_note_title' : 'agenda.note_title'),
            sub: $note->body,
            status: $proposed ? DisplayStatus::Proposed : DisplayStatus::Confirmed,
            onCalendar: false,
            href: $proposed && $note->source_document_id
                ? $this->route('dictation.show', $note->source_document_id)
                : $this->route('matters.show', $note->matter_id),
        );
    }

    /**
     * Restricts a source query to the matter and date window. $column holds
     * a date, or a timestamp when $timestamp is true.
     *
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    private function window(
        Builder $query,
        ?Matter $matter,
        ?CarbonImmutable $from,
        ?CarbonImmutable $to,
        bool $withProposed,
        string $column,
        bool $timestamp,
        ?string $proposed,
    ): Builder {
        if ($matter) {
            $query->where('matter_id', $matter->id);
        }

        if ($from === null && $to === null) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($column, $timestamp, $proposed, $from, $to, $withProposed) {
            $q->where(function (Builder $range) use ($column, $timestamp, $from, $to) {
                if ($from) {
                    $range->where($column, '>=', $timestamp ? $from->startOfDay()->toIso8601String() : $from->toDateString());
                }
                if ($to) {
                    $timestamp
                        ? $range->where($column, '<', $to->addDay()->startOfDay()->toIso8601String())
                        : $range->where($column, '<=', $to->toDateString());
                }
            });

            if ($withProposed && $proposed !== null) {
                $q->orWhere('status', $proposed);
            }
        });
    }

    private function route(string $name, int $id): ?string
    {
        return Route::has($name) ? route($name, $id, false) : null;
    }
}
