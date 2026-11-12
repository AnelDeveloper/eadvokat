<?php

namespace App\Demo;

use App\Enums\CostSheetStatus;
use App\Enums\DeadlineStatus;
use App\Enums\MailDirection;
use App\Models\CostSheet;
use App\Models\Deadline;
use App\Models\Document;
use App\Models\MailEntry;
use App\Models\Office;
use Illuminate\Support\Facades\Session;

/**
 * Which of the nine guide steps the visitor has completed in their demo office.
 * Mirrors the `done` array in the prototype's guide.
 */
final class DemoProgress
{
    /**
     * @return array<int, bool>
     */
    public function steps(): array
    {
        $captured = Document::query()->where('kind', 'court_document')->whereNotNull('path')->exists();
        $capturedMail = MailEntry::query()
            ->where('direction', MailDirection::In)
            ->whereIn('document_id', Document::query()->where('kind', 'court_document')->whereNotNull('path')->select('id'))
            ->pluck('id');
        $mailSaved = $capturedMail->isNotEmpty();
        $deadlineConfirmed = $this->deadlineConfirmed();
        $voiceSaved = Document::query()->where('kind', 'recording')->whereNotNull('extraction->saved')->exists();
        $outSaved = $this->submissionSaved();
        $costApproved = CostSheet::query()->where('status', CostSheetStatus::Approved)->exists();

        return [
            $captured,
            $captured,
            $mailSaved,
            $deadlineConfirmed,
            (bool) Session::get('demo.seen_calendar', false),
            $voiceSaved,
            $outSaved,
            (bool) Session::get('demo.seen_costs', false),
            $costApproved,
        ];
    }

    /**
     * Called by the Kalendar and Troškovnik screens of a demo office.
     */
    public function markSeen(Office $office, string $screen): void
    {
        if (! $office->is_demo) {
            return;
        }

        if ($screen === 'calendar' && $this->deadlineConfirmed()) {
            Session::put('demo.seen_calendar', true);
        }

        if ($screen === 'costs' && $this->submissionSaved()) {
            Session::put('demo.seen_costs', true);
        }
    }

    private function deadlineConfirmed(): bool
    {
        return Deadline::query()
            ->whereIn('status', [DeadlineStatus::Confirmed, DeadlineStatus::Fulfilled])
            ->whereIn('source_document_id', Document::query()->where('kind', 'court_document')->whereNotNull('path')->select('id'))
            ->exists();
    }

    private function submissionSaved(): bool
    {
        return MailEntry::query()
            ->where('direction', MailDirection::Out)
            ->where('received_or_sent_on', '>=', DemoScenario::TODAY)
            ->exists();
    }
}
