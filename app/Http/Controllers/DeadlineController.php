<?php

namespace App\Http\Controllers;

use App\Actions\ConfirmDeadline;
use App\Enums\DeadlineStatus;
use App\Http\Requests\ConfirmDeadlineRequest;
use App\Models\Deadline;
use App\Screens\DeadlineScreen;
use App\Support\Format;
use Illuminate\Http\RedirectResponse;
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

    public function confirm(ConfirmDeadlineRequest $request, Deadline $deadline, ConfirmDeadline $action): RedirectResponse
    {
        Gate::authorize('update', $deadline);

        $action->handle($this->user(), $deadline, $request->payload());

        Inertia::flash('toast', __('deadlines.confirmed', ['date' => Format::date($deadline->due_date)]));

        return to_route('matters.show', $deadline->matter_id);
    }
}
