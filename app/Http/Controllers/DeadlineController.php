<?php

namespace App\Http\Controllers;

use App\Actions\ConfirmDeadline;
use App\Enums\DeadlineStatus;
use App\Enums\Jurisdiction;
use App\Http\Requests\ConfirmDeadlineRequest;
use App\Models\Deadline;
use App\Support\Format;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DeadlineController extends Controller
{
    public function review(Deadline $deadline): Response|RedirectResponse
    {
        Gate::authorize('view', $deadline);

        if ($deadline->status === DeadlineStatus::Fulfilled) {
            return to_route('matters.show', $deadline->matter_id);
        }

        $deadline->load(['sourceDocument', 'sourceMailEntry']);
        $source = $deadline->sourceDocument;

        $meta = collect([
            $source ? __('deadlines.source_meta', [
                'name' => explode(' · ', $source->name)[0],
                'date' => Format::date($source->document_date),
            ]) : null,
            $deadline->sourceMailEntry?->register_number,
        ])->filter()->implode(' · ');

        return Inertia::render('flow/DeadlineProposal', [
            'deadline' => [
                'id' => $deadline->id,
                'matterId' => $deadline->matter_id,
                'title' => $deadline->title,
                'ruleText' => $deadline->rule_text,
                'days' => $deadline->days,
                'quote' => $deadline->source_quote,
                'highlight' => $deadline->source_highlight,
                'sourceMeta' => $meta,
                'sourceDocumentId' => $deadline->source_document_id,
                'jurisdiction' => $deadline->jurisdiction->value,
                'startDate' => $deadline->start_date->toDateString(),
                'dueDate' => $deadline->due_date->toDateString(),
                'remind3' => $deadline->remind_3_days,
                'remind1' => $deadline->remind_1_day,
                'isConfirmed' => $deadline->status === DeadlineStatus::Confirmed,
            ],
            'jurisdictions' => collect(Jurisdiction::cases())->map(fn (Jurisdiction $j) => [
                'value' => $j->value,
                'label' => $j->shortLabel(),
            ]),
        ]);
    }

    public function confirm(ConfirmDeadlineRequest $request, Deadline $deadline, ConfirmDeadline $action): RedirectResponse
    {
        Gate::authorize('update', $deadline);

        $action->handle($this->user(), $deadline, $request->payload());

        Inertia::flash('toast', __('deadlines.confirmed', ['date' => Format::date($deadline->due_date)]));

        return to_route('matters.show', $deadline->matter_id);
    }
}
