<?php

namespace App\Http\Controllers;

use App\Actions\RecordOutgoingSubmission;
use App\Http\Requests\RecordSubmissionRequest;
use App\Screens\SubmissionScreen;
use App\Support\Format;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SubmissionController extends Controller
{
    public function create(SubmissionScreen $screen): Response
    {
        return Inertia::render('flow/OutgoingSubmission', $screen->create($this->user()->office_id, (bool) $this->office()->is_demo));
    }

    public function store(RecordSubmissionRequest $request, RecordOutgoingSubmission $action): RedirectResponse
    {
        $matter = $request->matter();
        Gate::authorize('update', $matter);

        $payload = $request->payload();
        $action->handle($this->user(), $matter, $payload);

        Inertia::flash('toast', __('submission.saved', ['date' => Format::date($payload['reminder_on'])]));

        return to_route('matters.show', $matter);
    }
}
