<?php

namespace App\Http\Controllers;

use App\Actions\ConfirmNote;
use App\Models\Note;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class NoteController extends Controller
{
    public function confirm(Request $request, Note $note, ConfirmNote $action): RedirectResponse
    {
        Gate::authorize('update', $note);

        $validated = $request->validate(['body' => ['required', 'string', 'max:5000']], attributes: ['body' => 'bilješka']);

        $action->handle($this->user(), $note, $validated['body']);

        Inertia::flash('toast', __('dictation.note_confirmed'));

        return back();
    }
}
