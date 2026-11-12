<?php

namespace App\Http\Controllers;

use App\Actions\CaptureDocument;
use App\Actions\SaveIncomingMail;
use App\Enums\DeadlineStatus;
use App\Enums\MailDirection;
use App\Http\Requests\CaptureDocumentRequest;
use App\Http\Requests\SaveIncomingMailRequest;
use App\Models\Deadline;
use App\Models\Document;
use App\Models\MailEntry;
use App\Models\Matter;
use App\Services\Extraction\Extraction;
use App\Services\RegisterNumbers;
use App\Support\Clock;
use App\Support\Format;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class MailController extends Controller
{
    public function index(Request $request): Response
    {
        $direction = $request->query('smjer') === 'izlazna' ? MailDirection::Out : MailDirection::In;

        $entries = MailEntry::query()
            ->where('direction', $direction)
            ->with(['deadlines', 'matter'])
            ->orderByDesc('received_or_sent_on')
            ->orderByDesc('register_sequence')
            ->get()
            ->map(fn (MailEntry $entry) => [
                'id' => $entry->id,
                'number' => $entry->register_number,
                'date' => Format::date($entry->received_or_sent_on),
                'title' => $entry->doc_type,
                'sub' => $direction === MailDirection::Out && $entry->delivery_method
                    ? $entry->delivery_method->label().' · '.$entry->case_number
                    : $entry->court.' · '.$entry->case_number,
                ...$this->linkLine($entry),
                'documentId' => $entry->document_id,
            ]);

        return Inertia::render('mail/Index', [
            'direction' => $direction === MailDirection::In ? 'in' : 'out',
            'entries' => $entries,
        ]);
    }

    public function capture(): Response
    {
        return Inertia::render('flow/Capture', [
            'isDemo' => (bool) $this->office()->is_demo,
        ]);
    }

    public function store(CaptureDocumentRequest $request, CaptureDocument $capture): RedirectResponse
    {
        /** @var UploadedFile $file */
        $file = $request->file('file');
        $document = $capture->handle($this->user(), $file);

        return to_route('mail.review', $document);
    }

    public function review(Document $document, RegisterNumbers $numbers): Response|RedirectResponse
    {
        Gate::authorize('view', $document);

        if ($existing = MailEntry::query()->where('document_id', $document->id)->first()) {
            Inertia::flash('toast', __('mail.already_registered', ['number' => $existing->register_number]));

            return to_route('mail.index');
        }

        abort_if($document->extraction === null, 404);
        $extraction = Extraction::fromArray($document->extraction);
        $fields = $extraction->fields;

        return Inertia::render('flow/MailReview', [
            'documentId' => $document->id,
            'paragraphs' => $extraction->paragraphs,
            'quotes' => $extraction->quotes,
            'registerNumber' => $numbers->peek($this->user()->office_id, MailDirection::In, Clock::today()->year),
            'fields' => [
                'court' => $fields['court'],
                'case_number' => $fields['case_number'],
                'doc_type' => $fields['doc_type'],
                'document_date' => $fields['document_date'],
                'received_on' => $fields['received_on'],
            ],
            'receivedOnAssumed' => $fields['received_on_assumed'],
        ]);
    }

    public function save(SaveIncomingMailRequest $request, Document $document, SaveIncomingMail $action): RedirectResponse
    {
        Gate::authorize('update', $document);

        if ($existing = MailEntry::query()->where('document_id', $document->id)->first()) {
            Inertia::flash('toast', __('mail.already_registered', ['number' => $existing->register_number]));

            return to_route('mail.index');
        }

        /** @var Matter $matter */
        $matter = $request->matter();
        /** @var array{court: string, case_number: string, doc_type: string, document_date: string|null, received_on: string} $fields */
        $fields = $request->validated();
        [$entry, $deadline] = $action->handle($this->user(), $document, $matter, $fields);

        Inertia::flash('toast', __('mail.saved', ['number' => $entry->register_number]));

        return $deadline && Route::has('deadlines.review')
            ? to_route('deadlines.review', $deadline)
            : to_route('matters.show', $matter);
    }

    /**
     * @return array{link: string|null, linkTone: 'confirmed'|'proposed'|null}
     */
    private function linkLine(MailEntry $entry): array
    {
        if ($entry->direction === MailDirection::Out) {
            return $entry->proof_document_id || $entry->proof_note
                ? ['link' => __('mail.proof_attached', ['note' => $entry->proof_note]), 'linkTone' => 'confirmed']
                : ['link' => __('mail.proof_missing'), 'linkTone' => 'proposed'];
        }

        /** @var Deadline|null $deadline */
        $deadline = $entry->deadlines->first();

        if ($deadline === null) {
            return ['link' => null, 'linkTone' => null];
        }

        $date = Format::date($deadline->due_date);

        return match ($deadline->status) {
            DeadlineStatus::Proposed => ['link' => __('mail.linked_deadline_proposed', ['date' => $date]), 'linkTone' => 'proposed'],
            DeadlineStatus::Fulfilled => ['link' => __('mail.linked_deadline_fulfilled', ['date' => $date]), 'linkTone' => 'confirmed'],
            DeadlineStatus::Confirmed => ['link' => __('mail.linked_deadline_confirmed', ['date' => $date]), 'linkTone' => 'confirmed'],
        };
    }
}
