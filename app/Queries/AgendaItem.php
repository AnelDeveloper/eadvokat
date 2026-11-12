<?php

namespace App\Queries;

use App\Enums\DisplayStatus;
use App\Support\Format;
use Carbon\CarbonImmutable;

/**
 * One row of the matter timeline, the Danas briefing or the calendar.
 */
final readonly class AgendaItem
{
    public const KIND_EVENT = 'event';

    public const KIND_DEADLINE = 'deadline';

    public const KIND_REMINDER = 'reminder';

    public const KIND_MAIL = 'mail';

    public const KIND_NOTE = 'note';

    public function __construct(
        public string $key,
        public string $kind,
        public int $matterId,
        public CarbonImmutable $date,
        public ?string $time,
        public string $type,
        public string $title,
        public string $sub,
        public DisplayStatus $status,
        public bool $onCalendar,
        /** hearing, meeting or court_visit for events */
        public ?string $eventType = null,
        public ?string $href = null,
        public ?int $documentId = null,
        public ?string $toggleUrl = null,
    ) {}

    public function sortKey(): int
    {
        $minutes = $this->time ? ((int) substr($this->time, 0, 2)) * 60 + (int) substr($this->time, 3, 2) : 0;

        return $this->date->getTimestamp() + $minutes * 60;
    }

    public function isOn(CarbonImmutable $day): bool
    {
        return $this->date->isSameDay($day);
    }

    /**
     * @param  'brief'|'timeline'|'day'  $mode
     * @return array<string, mixed>
     */
    public function toRow(string $mode, CarbonImmutable $today): array
    {
        $when = match ($mode) {
            'timeline' => Format::date($this->date),
            'day' => $this->time ?? '—',
            'brief' => $this->isOn($today) ? ($this->time ?? __('agenda.today')) : Format::dayMonth($this->date),
        };

        return [
            'id' => $this->key,
            'when' => $when,
            'time' => $mode === 'timeline' ? $this->time : null,
            'type' => $mode === 'brief' ? null : $this->type,
            'title' => $this->title,
            'sub' => $this->sub,
            'status' => $this->status->value,
            'href' => $this->href,
            'documentId' => $this->documentId,
            'toggleUrl' => $this->toggleUrl,
        ];
    }
}
