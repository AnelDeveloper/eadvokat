<?php

namespace App\Actions;

use App\Enums\DeadlineStatus;
use App\Enums\DeliveryMethod;
use App\Enums\ItemStatus;
use App\Enums\MailDirection;
use App\Enums\ReminderKind;
use App\Models\Deadline;
use App\Models\Document;
use App\Models\MailEntry;
use App\Models\Matter;
use App\Models\Reminder;
use App\Models\User;
use App\Services\RegisterNumbers;
use App\Support\Audit;
use App\Support\Clock;
use App\Support\Format;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Registers a filed submission (outgoing mail) with its proof of filing,
 * marks the deadlines the lawyer says it fulfils, and sets a follow-up
 * reminder. Nothing is sent anywhere.
 */
final class RecordOutgoingSubmission
{
    public function __construct(
        private readonly RegisterNumbers $numbers,
        private readonly SuggestCostItem $suggestCost,
    ) {}

    /**
     * @param  array{doc_type: string, sent_on: string, delivery_method: DeliveryMethod, proof: UploadedFile|null, fulfils: array<int, int>, reminder_text: string, reminder_on: string}  $input
     */
    public function handle(User $user, Matter $matter, array $input): MailEntry
    {
        $proofPath = $input['proof']?->store("documents/{$user->office_id}", 'local');

        return DB::transaction(function () use ($user, $matter, $input, $proofPath) {
            $sentOn = CarbonImmutable::parse($input['sent_on']);
            [$sequence, $number] = $this->numbers->next($user->office_id, MailDirection::Out, $sentOn->year);
            $method = $input['delivery_method'];

            $proof = null;
            if ($input['proof'] !== null && $proofPath) {
                $proof = Document::query()->create([
                    'office_id' => $user->office_id,
                    'matter_id' => $matter->id,
                    'kind' => 'proof',
                    'name' => __('submission.proof_name', ['number' => $number]),
                    'disk' => 'local',
                    'path' => $proofPath,
                    'original_name' => $input['proof']->getClientOriginalName(),
                    'mime_type' => (string) $input['proof']->getMimeType(),
                    'size' => $input['proof']->getSize(),
                    'document_date' => $sentOn->toDateString(),
                    'uploaded_by' => $user->id,
                ]);
            }

            $proofNote = $proof ? __($method === DeliveryMethod::InPerson ? 'submission.proof_stamp' : 'submission.proof_receipt') : null;

            $entry = MailEntry::query()->create([
                'office_id' => $user->office_id,
                'matter_id' => $matter->id,
                'direction' => MailDirection::Out,
                'register_year' => $sentOn->year,
                'register_sequence' => $sequence,
                'register_number' => $number,
                'received_or_sent_on' => $sentOn->toDateString(),
                'document_date' => $sentOn->toDateString(),
                'court' => $matter->court,
                'case_number' => $matter->case_number,
                'doc_type' => $input['doc_type'],
                'note' => mb_strtolower($method->label()).' · '.__($proof ? 'submission.with_proof' : 'submission.without_proof'),
                'delivery_method' => $method,
                'document_id' => $proof?->id,
                'proof_document_id' => $proof?->id,
                'proof_note' => $proofNote,
                'status' => ItemStatus::Confirmed,
                'confirmed_by' => $user->id,
                'confirmed_at' => Clock::now(),
            ]);

            $deadlines = Deadline::query()
                ->where('matter_id', $matter->id)
                ->where('status', DeadlineStatus::Confirmed)
                ->whereIn('id', $input['fulfils'])
                ->get();

            foreach ($deadlines as $deadline) {
                $deadline->update([
                    'status' => DeadlineStatus::Fulfilled,
                    'fulfilled_by_mail_entry_id' => $entry->id,
                    'fulfilled_at' => Clock::now(),
                ]);

                // A fulfilled deadline leaves the briefing: drop its pending reminders.
                $deadline->reminders()->whereNull('checked_at')->delete();

                Audit::record($matter, 'deadline.fulfilled', __('audit.deadline_fulfilled', [
                    'date' => Format::date($deadline->due_date),
                    'number' => $number,
                ]), $deadline, ['status' => 'confirmed'], ['status' => 'fulfilled', 'fulfilled_by' => $number]);
            }

            $remindOn = CarbonImmutable::parse($input['reminder_on']);
            Reminder::query()->create([
                'office_id' => $user->office_id,
                'matter_id' => $matter->id,
                'remind_on' => $remindOn->toDateString(),
                'text' => $input['reminder_text'],
                'detail' => __('submission.reminder_detail', ['number' => $number, 'date' => Format::date(Clock::today())]),
                'kind' => ReminderKind::FollowUp,
                'source_type' => $entry->getMorphClass(),
                'source_id' => $entry->id,
                'created_by' => $user->id,
            ]);

            Audit::record($matter, 'mail_entry.outgoing', __($proof ? 'audit.outgoing_with_proof' : 'audit.outgoing_without_proof', [
                'number' => $number,
                'date' => Format::date($remindOn),
            ]), $entry, [], [
                'register_number' => $number,
                'doc_type' => $input['doc_type'],
                'sent_on' => $sentOn->toDateString(),
                'delivery_method' => $method->value,
                'proof' => $proof !== null,
                'fulfils' => $deadlines->pluck('id')->all(),
                'reminder_on' => $remindOn->toDateString(),
            ]);

            $this->suggestCost->handle(
                $matter,
                __('submission.cost_title', ['type' => $input['doc_type']]),
                $sentOn,
                $entry,
                __('submission.cost_source', ['number' => $number]),
                $proof?->id,
            );

            return $entry;
        });
    }
}
