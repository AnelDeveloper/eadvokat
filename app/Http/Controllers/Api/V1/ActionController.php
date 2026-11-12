<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\CaptureDocument;
use App\Actions\ConfirmDeadline;
use App\Actions\ConfirmEvent;
use App\Actions\ConfirmNote;
use App\Actions\Costs\ManageCostItems;
use App\Actions\Costs\ManageCostSheet;
use App\Actions\RecordDictation;
use App\Actions\RecordOutgoingSubmission;
use App\Actions\SaveHearingNote;
use App\Actions\SaveIncomingMail;
use App\Enums\EventType;
use App\Enums\Jurisdiction;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CostSheetController;
use App\Http\Controllers\DocumentController;
use App\Http\Requests\CaptureDocumentRequest;
use App\Http\Requests\ConfirmDeadlineRequest;
use App\Http\Requests\RecordSubmissionRequest;
use App\Http\Requests\SaveHearingNoteRequest;
use App\Http\Requests\SaveIncomingMailRequest;
use App\Models\CostItem;
use App\Models\Deadline;
use App\Models\Document;
use App\Models\Event;
use App\Models\MailEntry;
use App\Models\Matter;
use App\Models\Note;
use App\Models\Reminder;
use App\Screens\CostsScreen;
use App\Support\Clock;
use App\Support\Format;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Mutations for the mobile app. Every one runs the same domain action as the
 * web app and answers with an optional toast message and the next app path.
 */
class ActionController extends Controller
{
    public function capture(CaptureDocumentRequest $request, CaptureDocument $capture): JsonResponse
    {
        /** @var UploadedFile $file */
        $file = $request->file('file');
        $document = $capture->handle($this->user(), $file);

        return $this->done(null, "/posta/pregled/{$document->id}");
    }

    public function saveMail(SaveIncomingMailRequest $request, Document $document, SaveIncomingMail $action): JsonResponse
    {
        Gate::authorize('update', $document);

        if ($existing = MailEntry::query()->where('document_id', $document->id)->first()) {
            throw ValidationException::withMessages(['document' => __('mail.already_registered', ['number' => $existing->register_number])]);
        }

        /** @var Matter $matter */
        $matter = $request->matter();
        /** @var array{court: string, case_number: string, doc_type: string, document_date: string|null, received_on: string} $fields */
        $fields = $request->validated();
        [$entry, $deadline] = $action->handle($this->user(), $document, $matter, $fields);

        return $this->done(
            __('mail.saved', ['number' => $entry->register_number]),
            $deadline ? "/rokovi/{$deadline->id}/prijedlog" : "/predmeti/{$matter->id}",
        );
    }

    public function confirmDeadline(ConfirmDeadlineRequest $request, Deadline $deadline, ConfirmDeadline $action): JsonResponse
    {
        Gate::authorize('update', $deadline);
        $action->handle($this->user(), $deadline, $request->payload());

        return $this->done(__('deadlines.confirmed', ['date' => Format::date($deadline->due_date)]), "/predmeti/{$deadline->matter_id}");
    }

    public function toggleReminder(Reminder $reminder): JsonResponse
    {
        Gate::authorize('update', $reminder);

        $reminder->update($reminder->checked_at === null
            ? ['checked_at' => Clock::now(), 'checked_by' => $this->user()->id]
            : ['checked_at' => null, 'checked_by' => null]);

        return $this->done(null, null);
    }

    public function dictate(Request $request, RecordDictation $record): JsonResponse
    {
        $request->validate([
            'event_id' => ['required', 'integer'],
            'audio' => [$this->office()->is_demo ? 'nullable' : 'required', 'file', 'mimetypes:audio/webm,audio/ogg,audio/mp4,audio/x-m4a,audio/aac,audio/mpeg,audio/wav,audio/x-wav,video/webm,video/mp4', 'max:51200'],
        ]);

        $hearing = Event::query()->where('type', EventType::Hearing)->findOrFail($request->integer('event_id'));
        Gate::authorize('view', $hearing);

        $document = $record->handle($this->user(), $hearing, $request->file('audio'));

        return $this->done(null, "/diktat/{$document->id}");
    }

    public function saveDictation(SaveHearingNoteRequest $request, Document $document, SaveHearingNote $action): JsonResponse
    {
        Gate::authorize('update', $document);
        abort_unless($document->kind === 'recording', 404);

        $result = $action->handle($this->user(), $document, $request->payload());

        return $this->done(__($result['confirmed'] === 3 ? 'dictation.saved_all' : 'dictation.saved_some'), '/danas');
    }

    public function confirmEvent(Request $request, Event $event, ConfirmEvent $action): JsonResponse
    {
        Gate::authorize('update', $event);
        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'time' => ['required', 'date_format:H:i'],
            'location' => ['nullable', 'string', 'max:20'],
        ], attributes: ['date' => 'datum', 'time' => 'vrijeme']);

        $action->handle($this->user(), $event, [
            'date' => $validated['date'],
            'time' => $validated['time'],
            'location' => ($validated['location'] ?? '') !== '' ? $validated['location'] : null,
        ]);

        return $this->done(__('dictation.event_confirmed'), null);
    }

    public function confirmNote(Request $request, Note $note, ConfirmNote $action): JsonResponse
    {
        Gate::authorize('update', $note);
        $validated = $request->validate(['body' => ['required', 'string', 'max:5000']], attributes: ['body' => 'bilješka']);
        $action->handle($this->user(), $note, $validated['body']);

        return $this->done(__('dictation.note_confirmed'), null);
    }

    public function submit(RecordSubmissionRequest $request, RecordOutgoingSubmission $action): JsonResponse
    {
        $matter = $request->matter();
        Gate::authorize('update', $matter);
        $payload = $request->payload();
        $action->handle($this->user(), $matter, $payload);

        return $this->done(__('submission.saved', ['date' => Format::date($payload['reminder_on'])]), "/predmeti/{$matter->id}");
    }

    public function updateCostSheet(Request $request, Matter $matter, ManageCostSheet $action, CostsScreen $screen): JsonResponse
    {
        Gate::authorize('update', $matter);
        $validated = $request->validate([
            'jurisdiction' => ['required', Rule::enum(Jurisdiction::class)],
            'tariff_source' => ['nullable', 'string', 'max:500'],
        ], attributes: ['jurisdiction' => 'nadležnost', 'tariff_source' => 'izvor tarife']);

        $action->update($screen->sheet($matter), Jurisdiction::from($validated['jurisdiction']), $validated['tariff_source'] ?? null);

        return $this->done(null, null);
    }

    public function approveCostSheet(Matter $matter, ManageCostSheet $action, CostsScreen $screen): JsonResponse
    {
        Gate::authorize('update', $matter);
        $action->approve($this->user(), $screen->sheet($matter));

        return $this->done(__('costs.approved'), null);
    }

    public function updateCostItem(Request $request, CostItem $item, ManageCostItems $action): JsonResponse
    {
        Gate::authorize('update', $item);
        $validated = $request->validate([
            'amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
        ], attributes: ['amount' => 'iznos']);
        $amount = $validated['amount'] ?? null;
        $action->updateAmount($item, $amount === null ? null : number_format((float) $amount, 2, '.', ''));

        return $this->done(null, null);
    }

    public function confirmCostItem(CostItem $item, ManageCostItems $action): JsonResponse
    {
        Gate::authorize('update', $item);
        $action->confirm($this->user(), $item);

        return $this->done(__('costs.item_confirmed'), null);
    }

    public function revertCostItem(CostItem $item, ManageCostItems $action): JsonResponse
    {
        Gate::authorize('update', $item);
        $action->revertToReview($item);

        return $this->done(__('costs.item_reverted'), null);
    }

    public function removeCostItem(CostItem $item, ManageCostItems $action): JsonResponse
    {
        Gate::authorize('delete', $item);
        $action->remove($item);

        return $this->done(__('costs.item_removed'), null);
    }

    public function costPdf(Matter $matter): SymfonyResponse
    {
        return app(CostSheetController::class)->pdf($matter);
    }

    public function documentFile(Document $document): SymfonyResponse
    {
        return app(DocumentController::class)->file($document);
    }

    private function done(?string $message, ?string $next): JsonResponse
    {
        return response()->json(['message' => $message, 'next' => $next]);
    }
}
