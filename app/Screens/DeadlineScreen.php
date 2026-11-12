<?php

namespace App\Screens;

use App\Enums\DeadlineStatus;
use App\Enums\Jurisdiction;
use App\Models\Deadline;
use App\Support\Format;

/**
 * Prijedlog roka: the three-acknowledgement confirmation screen.
 */
final class DeadlineScreen
{
    /**
     * @return array<string, mixed>
     */
    public function data(Deadline $deadline): array
    {
        $deadline->load(['sourceDocument', 'sourceMailEntry']);
        $source = $deadline->sourceDocument;

        $meta = collect([
            $source ? __('deadlines.source_meta', [
                'name' => explode(' · ', $source->name)[0],
                'date' => Format::date($source->document_date),
            ]) : null,
            $deadline->sourceMailEntry?->register_number,
        ])->filter()->implode(' · ');

        return [
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
        ];
    }
}
