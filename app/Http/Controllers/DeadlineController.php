<?php

namespace App\Http\Controllers;

use App\Actions\ConfirmDeadline;
use App\Actions\ProposeDeadline;
use App\Enums\DeadlineStatus;
use App\Http\Requests\ConfirmDeadlineRequest;
use App\Http\Requests\ProposeDeadlineRequest;
use App\Models\Deadline;
use App\Models\MailEntry;
use App\Models\Matter;
use App\Screens\DeadlineScreen;
use App\Support\Clock;
use App\Support\Format;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DeadlineController extends Controller
{
    public function review(Deadline $deadline, DeadlineScreen $screen): Response|RedirectResponse
    {
        Gate::authorize('view', $deadline);

        if ($deadline->status === DeadlineStatus::Fulfilled) {
            return to_route('matters.show', $deadline->matter_id);
        }

        return Inertia::render('flow/DeadlineProposal', $screen->data($deadline));
    }

    public function create(Request $request, Matter $matter): Response
    {
        Gate::authorize('update', $matter);

        $entry = $request->integer('unos')
            ? MailEntry::query()->where('matter_id', $matter->id)->find($request->integer('unos'))
            : null;
        $today = Clock::today()->toDateString();

        return Inertia::render('matters/CreateDeadline', [
            'matter' => ['id' => $matter->id, 'caseNumber' => $matter->case_number, 'title' => $matter->title],
            'entry' => $entry ? [
                'id' => $entry->id,
                'number' => $entry->register_number,
                'docType' => $entry->doc_type,
                'receivedOn' => $entry->received_or_sent_on->toDateString(),
                'documentId' => $entry->document_id,
            ] : null,
            'today' => $today,
        ]);
    }

    public function store(ProposeDeadlineRequest $request, Matter $matter, ProposeDeadline $action): RedirectResponse
    {
        Gate::authorize('update', $matter);
        $deadline = $action->handle($matter, $request->payload());

        return to_route('deadlines.review', $deadline);
    }

    public function confirm(ConfirmDeadlineRequest $request, Deadline $deadline, ConfirmDeadline $action): RedirectResponse
    {
        Gate::authorize('update', $deadline);

        $action->handle($this->user(), $deadline, $request->payload());

        Inertia::flash('toast', __('deadlines.confirmed', ['date' => Format::date($deadline->due_date)]));

        return to_route('matters.show', $deadline->matter_id);
    }
}
