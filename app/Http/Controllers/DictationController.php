<?php

namespace App\Http\Controllers;

use App\Actions\RecordDictation;
use App\Actions\SaveHearingNote;
use App\Enums\DeadlineStatus;
use App\Enums\EventType;
use App\Enums\ItemStatus;
use App\Http\Requests\SaveHearingNoteRequest;
use App\Models\Deadline;
use App\Models\Document;
use App\Models\Event;
use App\Models\Note;
use App\Services\Speech\Transcription;
use App\Support\Clock;
use App\Support\Format;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DictationController extends Controller
{
    public function create(): Response
    {
        $hearing = $this->todaysHearing();

        return Inertia::render('flow/Dictation', [
            'hearing' => $hearing ? [
                'id' => $hearing->id,
                'label' => $hearing->matter->case_number.' · '.__('dictation.hearing_label', [
                    'date' => Format::date($hearing->starts_at),
                    'time' => $hearing->starts_at->setTimezone(config('app.timezone'))->format('H:i'),
                ]),
            ] : null,
            'isDemo' => (bool) $this->office()->is_demo,
        ]);
    }

    public function store(Request $request, RecordDictation $record): RedirectResponse
    {
        $validated = $request->validate([
            'event_id' => ['required', 'integer'],
            'audio' => [$this->office()->is_demo ? 'nullable' : 'required', 'file', 'mimetypes:audio/webm,audio/ogg,audio/mp4,audio/mpeg,audio/wav,audio/x-wav,video/webm', 'max:51200'],
        ]);

        $hearing = Event::query()->where('type', EventType::Hearing)->findOrFail($request->integer('event_id'));
        Gate::authorize('view', $hearing);

        $document = $record->handle($this->user(), $hearing, $request->file('audio'));

        return to_route('dictation.show', $document);
    }

    public function show(Document $document): Response
    {
        Gate::authorize('view', $document);
        abort_unless($document->kind === 'recording' && is_array($document->extraction), 404);

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

        return Inertia::render('flow/DictationResult', [
            'documentId' => $document->id,
            'proposal' => $proposal,
            'saved' => $saved ? $this->savedState($saved) : null,
        ]);
    }

    public function save(SaveHearingNoteRequest $request, Document $document, SaveHearingNote $action): RedirectResponse
    {
        Gate::authorize('update', $document);
        abort_unless($document->kind === 'recording', 404);

        $result = $action->handle($this->user(), $document, $request->payload());

        Inertia::flash('toast', __($result['confirmed'] === 3 ? 'dictation.saved_all' : 'dictation.saved_some'));

        return to_route('today');
    }

    /**
     * @param  array{event_id: int, deadline_id: int, note_id: int}  $saved
     * @return array<string, mixed>
     */
    private function savedState(array $saved): array
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

    private function todaysHearing(): ?Event
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
