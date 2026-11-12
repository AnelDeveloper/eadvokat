<?php

namespace App\Actions;

use App\Enums\DeadlineStatus;
use App\Enums\EventType;
use App\Enums\ItemStatus;
use App\Enums\NoteSource;
use App\Models\Deadline;
use App\Models\Document;
use App\Models\Event;
use App\Models\Note;
use App\Models\User;
use App\Services\Speech\Transcription;
use App\Support\Audit;
use App\Support\Clock;
use App\Support\Format;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Saves the three independent proposals from a dictated hearing note.
 * Each one is active only if the lawyer confirmed it; the rest stay proposals.
 */
final class SaveHearingNote
{
    public function __construct(
        private readonly ConfirmDeadline $confirmDeadline,
        private readonly SuggestCostItem $suggestCost,
    ) {}

    /**
     * @param  array{
     *     event: array{date: string, time: string, location: string|null, confirmed: bool},
     *     deadline: array{due_date: string, rule_ack: bool, start_ack: bool, due_ack: bool, confirmed: bool},
     *     note: array{body: string, confirmed: bool}
     * }  $input
     * @return array{event: Event, deadline: Deadline, note: Note, confirmed: int}
     */
    public function handle(User $user, Document $recording, array $input): array
    {
        $data = $recording->extraction ?? [];

        if (($data['saved'] ?? null) !== null) {
            throw ValidationException::withMessages(['recording' => __('dictation.already_saved')]);
        }

        if ($input['event']['confirmed'] && $input['event']['location'] === null) {
            throw ValidationException::withMessages(['event.location' => __('dictation.room_unresolved')]);
        }

        $transcription = Transcription::fromArray($data);
        /** @var Event $hearing */
        $hearing = Event::query()->findOrFail($data['event_id']);
        $matter = $hearing->matter;

        return DB::transaction(function () use ($user, $recording, $input, $transcription, $hearing, $matter, $data) {
            $proposal = $transcription->event;
            $heard = $proposal['location_heard'];
            $chosen = $input['event']['location'];
            $startsAt = CarbonImmutable::parse($input['event']['date'].' '.$input['event']['time'], config('app.timezone'));

            if ($chosen !== null) {
                Audit::record($matter, 'voice.room_resolved', $chosen !== $heard
                    ? __('audit.room_corrected', ['old' => $heard, 'new' => $chosen])
                    : __('audit.room_confirmed', ['room' => $chosen]), $recording, ['location' => $heard], ['location' => $chosen]);
            }

            // The hearing that was dictated about now carries its outcome.
            $oldOutcome = $hearing->outcome;
            $hearing->update([
                'outcome' => $transcription->hearingOutcome['outcome'],
                'outcome_detail' => __('dictation.new_term', ['date' => Format::date($startsAt), 'time' => $startsAt->format('H:i')]),
            ]);
            Audit::record($matter, 'event.outcome', __('audit.hearing_outcome', [
                'date' => Format::date($hearing->starts_at),
                'outcome' => $hearing->outcome,
            ]), $hearing, ['outcome' => $oldOutcome], ['outcome' => $hearing->outcome]);

            $eventConfirmed = $input['event']['confirmed'];
            $event = Event::query()->create([
                'office_id' => $matter->office_id,
                'matter_id' => $matter->id,
                'type' => EventType::Hearing,
                'title' => $proposal['title'],
                'starts_at' => $startsAt,
                'location' => __('dictation.room', ['room' => $chosen ?? $heard]),
                'venue' => $proposal['venue'],
                'uncertain' => $chosen === null ? ['location' => $proposal['location_options']] : null,
                'source_document_id' => $recording->id,
                'source_quote' => $proposal['quote'],
                'status' => $eventConfirmed ? ItemStatus::Confirmed : ItemStatus::Proposed,
                'confirmed_by' => $eventConfirmed ? $user->id : null,
                'confirmed_at' => $eventConfirmed ? Clock::now() : null,
            ]);

            $hint = $transcription->deadline;
            $start = CarbonImmutable::parse($hearing->starts_at->toDateString());
            $deadline = Deadline::query()->create([
                'office_id' => $matter->office_id,
                'matter_id' => $matter->id,
                'title' => $hint['title'],
                'rule_text' => $hint['rule_text'],
                'days' => $hint['days'],
                'basis' => $hint['basis'],
                'jurisdiction' => $matter->jurisdiction,
                'source_document_id' => $recording->id,
                'source_quote' => $hint['quote'],
                'source_highlight' => $hint['highlight'],
                'start_date' => $start->toDateString(),
                'due_date' => $input['deadline']['due_date'],
                'due_date_manually_changed' => ! $start->addDays($hint['days'])->isSameDay(CarbonImmutable::parse($input['deadline']['due_date'])),
                'status' => DeadlineStatus::Proposed,
            ]);

            if ($input['deadline']['confirmed']) {
                $this->confirmDeadline->handle($user, $deadline, [
                    'jurisdiction' => $matter->jurisdiction,
                    'start_date' => $start->toDateString(),
                    'due_date' => $input['deadline']['due_date'],
                    'rule_ack' => $input['deadline']['rule_ack'],
                    'start_ack' => $input['deadline']['start_ack'],
                    'due_ack' => $input['deadline']['due_ack'],
                    'remind_3_days' => true,
                    'remind_1_day' => true,
                ]);
            }

            $noteConfirmed = $input['note']['confirmed'];
            $note = Note::query()->create([
                'office_id' => $matter->office_id,
                'matter_id' => $matter->id,
                'event_id' => $hearing->id,
                'body' => $input['note']['body'],
                'transcript' => $transcription->text,
                'source' => NoteSource::Voice,
                'source_document_id' => $recording->id,
                'status' => $noteConfirmed ? ItemStatus::Confirmed : ItemStatus::Proposed,
                'confirmed_by' => $noteConfirmed ? $user->id : null,
                'confirmed_at' => $noteConfirmed ? Clock::now() : null,
            ]);

            $this->suggestCost->handle(
                $matter,
                __('dictation.cost_title'),
                $start,
                $hearing,
                __('dictation.cost_source'),
                $recording->id,
            );

            $confirmed = (int) $eventConfirmed + (int) $input['deadline']['confirmed'] + (int) $noteConfirmed;

            Audit::record($matter, 'voice.saved', __('audit.voice_saved', ['count' => $confirmed]), $recording, [], [
                'event' => ['date' => $startsAt->toDateTimeString(), 'location' => $event->location, 'confirmed' => $eventConfirmed],
                'deadline' => ['due_date' => $input['deadline']['due_date'], 'confirmed' => $input['deadline']['confirmed']],
                'note' => ['confirmed' => $noteConfirmed],
            ]);

            $recording->update(['extraction' => [...$data, 'saved' => [
                'event_id' => $event->id,
                'deadline_id' => $deadline->id,
                'note_id' => $note->id,
            ]]]);

            return ['event' => $event, 'deadline' => $deadline->fresh() ?? $deadline, 'note' => $note, 'confirmed' => $confirmed];
        });
    }
}
