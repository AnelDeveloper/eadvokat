<?php

namespace App\Http\Controllers;

use App\Actions\RecordDictation;
use App\Actions\SaveHearingNote;
use App\Enums\EventType;
use App\Http\Requests\SaveHearingNoteRequest;
use App\Models\Document;
use App\Models\Event;
use App\Screens\DictationScreens;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DictationController extends Controller
{
    public function create(DictationScreens $screens): Response
    {
        return Inertia::render('flow/Dictation', $screens->create((bool) $this->office()->is_demo));
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

    public function show(Document $document, DictationScreens $screens): Response
    {
        Gate::authorize('view', $document);
        abort_unless($document->kind === 'recording' && is_array($document->extraction), 404);

        return Inertia::render('flow/DictationResult', $screens->show($document));
    }

    public function save(SaveHearingNoteRequest $request, Document $document, SaveHearingNote $action): RedirectResponse
    {
        Gate::authorize('update', $document);
        abort_unless($document->kind === 'recording', 404);

        $result = $action->handle($this->user(), $document, $request->payload());

        Inertia::flash('toast', __($result['confirmed'] === 3 ? 'dictation.saved_all' : 'dictation.saved_some'));

        return to_route('today');
    }
}
