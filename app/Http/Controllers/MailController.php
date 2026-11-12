<?php

namespace App\Http\Controllers;

use App\Actions\CaptureDocument;
use App\Actions\SaveIncomingMail;
use App\Enums\MailDirection;
use App\Http\Requests\CaptureDocumentRequest;
use App\Http\Requests\SaveIncomingMailRequest;
use App\Models\Document;
use App\Models\MailEntry;
use App\Models\Matter;
use App\Screens\MailScreens;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class MailController extends Controller
{
    public function index(Request $request, MailScreens $screens): Response
    {
        $direction = $request->query('smjer') === 'izlazna' ? MailDirection::Out : MailDirection::In;

        return Inertia::render('mail/Index', $screens->index($direction));
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

    public function review(Document $document, MailScreens $screens): Response|RedirectResponse
    {
        Gate::authorize('view', $document);

        if ($existing = MailEntry::query()->where('document_id', $document->id)->first()) {
            Inertia::flash('toast', __('mail.already_registered', ['number' => $existing->register_number]));

            return to_route('mail.index');
        }

        abort_if($document->extraction === null, 404);

        return Inertia::render('flow/MailReview', $screens->review($document, $this->user()->office_id));
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

        return $deadline
            ? to_route('deadlines.review', $deadline)
            : to_route('deadlines.create', ['matter' => $matter, 'unos' => $entry->id]);
    }
}
