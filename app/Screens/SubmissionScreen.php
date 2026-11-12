<?php

namespace App\Screens;

use App\Enums\DeadlineStatus;
use App\Enums\DeliveryMethod;
use App\Enums\MailDirection;
use App\Models\Deadline;
use App\Models\Matter;
use App\Services\RegisterNumbers;
use App\Support\Clock;
use App\Support\Format;

/**
 * Izlazni podnesak form data.
 */
final class SubmissionScreen
{
    public function __construct(private readonly RegisterNumbers $numbers) {}

    /**
     * @return array<string, mixed>
     */
    public function create(int $officeId, bool $isDemo): array
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

        return [
            'matters' => $matters,
            'nextNumber' => $this->numbers->peek($officeId, MailDirection::Out, $today->year),
            'today' => $today->toDateString(),
            'defaults' => [
                // The demo office starts from the prototype's scenario.
                'docType' => $isDemo ? 'Podnesak — dostava adrese svjedoka' : '',
                'reminderText' => __('submission.default_reminder'),
            ],
            'deliveryMethods' => collect(DeliveryMethod::cases())->map(fn (DeliveryMethod $m) => [
                'value' => $m->value,
                'label' => $m->label(),
            ]),
        ];
    }
}
