<?php

namespace App\Screens;

use App\Enums\DeadlineStatus;
use App\Enums\MailDirection;
use App\Models\Deadline;
use App\Models\Document;
use App\Models\MailEntry;
use App\Services\Extraction\Extraction;
use App\Services\RegisterNumbers;
use App\Support\Clock;
use App\Support\Format;

/**
 * Knjiga pošte and the review of a captured document.
 */
final class MailScreens
{
    public function __construct(private readonly RegisterNumbers $numbers) {}

    /**
     * @return array<string, mixed>
     */
    public function index(MailDirection $direction): array
    {
        $entries = MailEntry::query()
            ->where('direction', $direction)
            ->with(['deadlines', 'matter'])
            ->orderByDesc('received_or_sent_on')
            ->orderByDesc('register_sequence')
            ->get()
            ->map(fn (MailEntry $entry) => [
                'id' => $entry->id,
                'number' => $entry->register_number,
                'date' => Format::date($entry->received_or_sent_on),
                'title' => $entry->doc_type,
                'sub' => $direction === MailDirection::Out && $entry->delivery_method
                    ? $entry->delivery_method->label().' · '.$entry->case_number
                    : $entry->court.' · '.$entry->case_number,
                ...$this->linkLine($entry),
                'documentId' => $entry->document_id,
            ]);

        return [
            'direction' => $direction === MailDirection::In ? 'in' : 'out',
            'entries' => $entries,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function review(Document $document, int $officeId): array
    {
        abort_if($document->extraction === null, 404);
        $extraction = Extraction::fromArray($document->extraction);
        $fields = $extraction->fields;

        return [
            'documentId' => $document->id,
            'paragraphs' => $extraction->paragraphs,
            'quotes' => $extraction->quotes,
            'registerNumber' => $this->numbers->peek($officeId, MailDirection::In, Clock::today()->year),
            'fields' => [
                'court' => $fields['court'],
                'case_number' => $fields['case_number'],
                'doc_type' => $fields['doc_type'],
                'document_date' => $fields['document_date'],
                'received_on' => $fields['received_on'],
            ],
            'receivedOnAssumed' => $fields['received_on_assumed'],
        ];
    }

    /**
     * @return array{link: string|null, linkTone: 'confirmed'|'proposed'|null}
     */
    private function linkLine(MailEntry $entry): array
    {
        if ($entry->direction === MailDirection::Out) {
            return $entry->proof_document_id || $entry->proof_note
                ? ['link' => __('mail.proof_attached', ['note' => $entry->proof_note]), 'linkTone' => 'confirmed']
                : ['link' => __('mail.proof_missing'), 'linkTone' => 'proposed'];
        }

        /** @var Deadline|null $deadline */
        $deadline = $entry->deadlines->first();

        if ($deadline === null) {
            return ['link' => null, 'linkTone' => null];
        }

        $date = Format::date($deadline->due_date);

        return match ($deadline->status) {
            DeadlineStatus::Proposed => ['link' => __('mail.linked_deadline_proposed', ['date' => $date]), 'linkTone' => 'proposed'],
            DeadlineStatus::Fulfilled => ['link' => __('mail.linked_deadline_fulfilled', ['date' => $date]), 'linkTone' => 'confirmed'],
            DeadlineStatus::Confirmed => ['link' => __('mail.linked_deadline_confirmed', ['date' => $date]), 'linkTone' => 'confirmed'],
        };
    }
}
