<?php

namespace App\Actions;

use App\Enums\DeadlineStatus;
use App\Models\Deadline;
use App\Models\MailEntry;
use App\Models\Matter;
use App\Support\Audit;
use App\Support\Format;
use Carbon\CarbonImmutable;

/**
 * A deadline entered by hand. Like every deadline it starts as a proposal and
 * becomes active only through the three-acknowledgement confirmation.
 */
final class ProposeDeadline
{
    /**
     * @param  array{title: string, rule_text: string, days: int|null, start_date: string, due_date: string, source_quote: string|null, mail_entry_id: int|null}  $input
     */
    public function handle(Matter $matter, array $input): Deadline
    {
        $entry = $input['mail_entry_id'] ? MailEntry::query()->where('matter_id', $matter->id)->find($input['mail_entry_id']) : null;
        $start = CarbonImmutable::parse($input['start_date']);

        $deadline = Deadline::query()->create([
            'office_id' => $matter->office_id,
            'matter_id' => $matter->id,
            'title' => $input['title'],
            'rule_text' => $input['rule_text'],
            'days' => $input['days'],
            'basis' => $input['days'] ? __('deadlines.manual_basis', ['days' => $input['days']]) : __('deadlines.manual_basis_open'),
            'jurisdiction' => $matter->jurisdiction,
            'source_document_id' => $entry?->document_id,
            'source_mail_entry_id' => $entry?->id,
            'source_quote' => $input['source_quote'],
            'start_date' => $start->toDateString(),
            'due_date' => $input['due_date'],
            'due_date_manually_changed' => $input['days'] === null || ! $start->addDays($input['days'])->isSameDay(CarbonImmutable::parse($input['due_date'])),
            'status' => DeadlineStatus::Proposed,
        ]);

        Audit::record($matter, 'deadline.proposed', __('audit.deadline_entered', ['date' => Format::date($deadline->due_date)]), $deadline, [], [
            'title' => $deadline->title,
            'start_date' => $deadline->start_date->toDateString(),
            'due_date' => $deadline->due_date->toDateString(),
            'source_mail_entry' => $entry?->register_number,
        ]);

        return $deadline;
    }
}
