<?php

namespace App\Actions;

use App\Enums\DeadlineStatus;
use App\Enums\ItemStatus;
use App\Enums\MailDirection;
use App\Models\Deadline;
use App\Models\Document;
use App\Models\MailEntry;
use App\Models\Matter;
use App\Models\User;
use App\Services\Extraction\Extraction;
use App\Services\RegisterNumbers;
use App\Support\Audit;
use App\Support\Clock;
use App\Support\Format;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Saves a reviewed incoming document into the mail register and turns the
 * extractor's deadline hint into a *proposed* deadline (never active).
 */
final class SaveIncomingMail
{
    public function __construct(private readonly RegisterNumbers $numbers) {}

    /**
     * @param  array{court: string, case_number: string, doc_type: string, document_date: string|null, received_on: string}  $fields
     * @return array{0: MailEntry, 1: Deadline|null}
     */
    public function handle(User $user, Document $document, Matter $matter, array $fields): array
    {
        return DB::transaction(function () use ($user, $document, $matter, $fields) {
            $receivedOn = CarbonImmutable::parse($fields['received_on']);
            [$sequence, $number] = $this->numbers->next($user->office_id, MailDirection::In, $receivedOn->year);
            $extraction = $document->extraction ? Extraction::fromArray($document->extraction) : null;

            $entry = MailEntry::query()->create([
                'office_id' => $user->office_id,
                'matter_id' => $matter->id,
                'direction' => MailDirection::In,
                'register_year' => $receivedOn->year,
                'register_sequence' => $sequence,
                'register_number' => $number,
                'received_or_sent_on' => $receivedOn->toDateString(),
                'document_date' => $fields['document_date'],
                'court' => $fields['court'],
                'case_number' => $fields['case_number'],
                'doc_type' => $fields['doc_type'],
                'note' => __('mail.reviewed_note'),
                'document_id' => $document->id,
                'source_quote' => $extraction?->deadline['quote'] ?? null,
                'status' => ItemStatus::Confirmed,
                'confirmed_by' => $user->id,
                'confirmed_at' => Clock::now(),
            ]);

            $document->update([
                'matter_id' => $matter->id,
                'name' => ($extraction->name ?? $document->name).' · '.$number,
            ]);

            Audit::record($matter, 'mail_entry.saved', __('audit.mail_saved', ['number' => $number]), $entry, [], [
                'register_number' => $number,
                'court' => $entry->court,
                'case_number' => $entry->case_number,
                'doc_type' => $entry->doc_type,
                'document_date' => $fields['document_date'],
                'received_on' => $receivedOn->toDateString(),
                'extracted' => $extraction?->fields,
            ]);

            $deadline = null;
            $hint = $extraction?->deadline;

            if ($hint !== null) {
                $deadline = Deadline::query()->create([
                    'office_id' => $user->office_id,
                    'matter_id' => $matter->id,
                    'title' => $hint['title'],
                    'rule_text' => $hint['rule_text'],
                    'days' => $hint['days'],
                    'basis' => $hint['basis'],
                    'jurisdiction' => $matter->jurisdiction,
                    'source_document_id' => $document->id,
                    'source_mail_entry_id' => $entry->id,
                    'source_quote' => $hint['quote'],
                    'source_highlight' => $hint['highlight'],
                    'start_date' => $receivedOn->toDateString(),
                    // Rule 3: receipt day not counted; last day = start + N calendar days.
                    'due_date' => $receivedOn->addDays($hint['days'])->toDateString(),
                    'status' => DeadlineStatus::Proposed,
                ]);

                Audit::record($matter, 'deadline.proposed', __('audit.deadline_proposed', [
                    'date' => Format::date($deadline->due_date),
                    'number' => $number,
                ]), $deadline, [], ['due_date' => $deadline->due_date->toDateString(), 'status' => 'proposed']);
            }

            return [$entry, $deadline];
        });
    }
}
