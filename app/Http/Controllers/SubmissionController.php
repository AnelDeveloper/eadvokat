<?php

namespace App\Http\Controllers;

use App\Actions\RecordOutgoingSubmission;
use App\Enums\DeadlineStatus;
use App\Enums\DeliveryMethod;
use App\Enums\MailDirection;
use App\Http\Requests\RecordSubmissionRequest;
use App\Models\Deadline;
use App\Models\Matter;
use App\Services\RegisterNumbers;
use App\Support\Clock;
use App\Support\Format;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SubmissionController extends Controller
{
    public function create(RegisterNumbers $numbers): Response
    {
        $today = Clock::today();

        $matters = Matter::query()->orderBy('title')->get()->map(fn (Matter $matter) => [
            'id' => $matter->id,
            'caseNumber' => $matter->case_number,
            'title' => $matter->title,
            'openDeadlines' => Deadline::query()
                ->where('matter_id', $matter->id)
                ->where('status', DeadlineStatus::Confirmed)
                ->orderBy('due_date')
                ->get()
                ->map(fn (Deadline $d) => ['id' => $d->id, 'due' => Format::date($d->due_date), 'title' => $d->title]),
        ]);

        return Inertia::render('flow/OutgoingSubmission', [
            'matters' => $matters,
            'nextNumber' => $numbers->peek($this->user()->office_id, MailDirection::Out, $today->year),
            'today' => $today->toDateString(),
            'defaults' => [
                // The demo office starts from the prototype's scenario.
                'docType' => $this->office()->is_demo ? 'Podnesak — dostava adrese svjedoka' : '',
                'reminderText' => __('submission.default_reminder'),
            ],
            'deliveryMethods' => collect(DeliveryMethod::cases())->map(fn (DeliveryMethod $m) => [
                'value' => $m->value,
                'label' => $m->label(),
            ]),
        ]);
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
