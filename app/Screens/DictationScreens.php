<?php

namespace App\Screens;

use App\Enums\DeadlineStatus;
use App\Enums\EventType;
use App\Enums\ItemStatus;
use App\Models\Deadline;
use App\Models\Document;
use App\Models\Event;
use App\Models\Note;
use App\Services\Speech\Transcription;
use App\Support\Clock;
use App\Support\Format;
use Carbon\CarbonImmutable;

/**
 * Bilješka s ročišta: recording screen and the three proposals.
 */
final class DictationScreens
{
    /**
     * @return array<string, mixed>
     */
    public function create(bool $isDemo): array
    {
        $hearing = $this->todaysHearing();

        return [
            'hearing' => $hearing ? [
                'id' => $hearing->id,
                'label' => $hearing->matter->case_number.' · '.__('dictation.hearing_label', [
                    'date' => Format::date($hearing->starts_at),
                    'time' => $hearing->starts_at->setTimezone(config('app.timezone'))->format('H:i'),
                ]),
            ] : null,
            'isDemo' => $isDemo,
            'available' => $isDemo,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function show(Document $document): array
    {

        $data = $document->extraction;
        $transcription = Transcription::fromArray($data);
        /** @var Event $hearing */
        $hearing = Event::query()->findOrFail($data['event_id']);
        $start = CarbonImmutable::parse($hearing->starts_at->setTimezone(config('app.timezone'))->toDateString());
        $saved = $data['saved'] ?? null;

        $proposal = [
            'transcript' => $transcription->text,
            'event' => [
                'title' => $transcription->event['title'],
                'date' => $transcription->event['date'],
                'time' => $transcription->event['time'],
                'heard' => $transcription->event['location_heard'],
                'options' => $transcription->event['location_options'],
            ],
            'deadline' => [
                'title' => $transcription->deadline['title'],
                'ruleText' => $transcription->deadline['rule_text'],
                'highlight' => $transcription->deadline['highlight'],
                'days' => $transcription->deadline['days'],
                'startDate' => $start->toDateString(),
                'dueDate' => $start->addDays($transcription->deadline['days'])->toDateString(),
            ],
            'note' => $transcription->note['body'],
            'hearingDate' => Format::date($start),
        ];

        return [
            'documentId' => $document->id,
            'proposal' => $proposal,
            'saved' => $saved ? $this->savedState($saved) : null,
        ];
    }

    /**
     * @param  array{event_id: int, deadline_id: int, note_id: int}  $saved
     * @return array<string, mixed>
     */
    public function savedState(array $saved): array
    {
        $event = Event::query()->find($saved['event_id']);
        $deadline = Deadline::query()->find($saved['deadline_id']);
        $note = Note::query()->find($saved['note_id']);

        return [
            'event' => $event ? [
                'id' => $event->id,
                'date' => $event->starts_at->setTimezone(config('app.timezone'))->toDateString(),
                'time' => $event->starts_at->setTimezone(config('app.timezone'))->format('H:i'),
                'room' => $event->uncertain === null ? preg_replace('/\D/', '', (string) $event->location) : null,
                'confirmed' => $event->status === ItemStatus::Confirmed,
            ] : null,
            'deadline' => $deadline ? [
                'id' => $deadline->id,
                'dueDate' => $deadline->due_date->toDateString(),
                'status' => $deadline->status->value,
                'confirmed' => $deadline->status !== DeadlineStatus::Proposed,
            ] : null,
            'note' => $note ? [
                'id' => $note->id,
                'body' => $note->body,
                'confirmed' => $note->status === ItemStatus::Confirmed,
            ] : null,
        ];
    }

    public function todaysHearing(): ?Event
    {
        $today = Clock::today();

        return Event::query()
            ->with('matter')
            ->where('type', EventType::Hearing)
            ->whereBetween('starts_at', [$today, $today->endOfDay()])
            ->orderBy('starts_at')
            ->first();
    }
}
