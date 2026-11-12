<?php

namespace App\Actions;

use App\Enums\DeadlineStatus;
use App\Enums\Jurisdiction;
use App\Enums\ReminderKind;
use App\Models\Deadline;
use App\Models\Reminder;
use App\Models\User;
use App\Support\Audit;
use App\Support\Clock;
use App\Support\Format;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Rule 2: a deadline becomes active only when the lawyer separately
 * acknowledges the rule and jurisdiction, the start date and the last day.
 * Enforced here, in ConfirmDeadlineRequest and by a database check.
 */
final class ConfirmDeadline
{
    /**
     * @param  array{jurisdiction: Jurisdiction, start_date: string, due_date: string, rule_ack: bool, start_ack: bool, due_ack: bool, remind_3_days: bool, remind_1_day: bool}  $input
     */
    public function handle(User $user, Deadline $deadline, array $input): Deadline
    {
        if (! ($input['rule_ack'] && $input['start_ack'] && $input['due_ack'])) {
            throw ValidationException::withMessages(['acknowledgements' => __('deadlines.needs_three')]);
        }

        if ($deadline->status === DeadlineStatus::Fulfilled) {
            throw ValidationException::withMessages(['deadline' => __('deadlines.already_fulfilled')]);
        }

        return DB::transaction(function () use ($user, $deadline, $input) {
            $start = CarbonImmutable::parse($input['start_date']);
            $due = CarbonImmutable::parse($input['due_date']);
            // Rule 3: last day = start + N calendar days; anything else is a manual change.
            $manual = $deadline->days === null || ! $start->addDays($deadline->days)->isSameDay($due);

            $old = [
                'status' => $deadline->status->value,
                'jurisdiction' => $deadline->jurisdiction->value,
                'start_date' => $deadline->start_date->toDateString(),
                'due_date' => $deadline->due_date->toDateString(),
                'remind_3_days' => $deadline->remind_3_days,
                'remind_1_day' => $deadline->remind_1_day,
            ];
            $wasConfirmed = $deadline->status === DeadlineStatus::Confirmed;
            $oldJurisdiction = $deadline->jurisdiction;

            $deadline->update([
                'jurisdiction' => $input['jurisdiction'],
                'start_date' => $start->toDateString(),
                'due_date' => $due->toDateString(),
                'due_date_manually_changed' => $manual,
                'remind_3_days' => $input['remind_3_days'],
                'remind_1_day' => $input['remind_1_day'],
                'rule_ack' => true,
                'start_ack' => true,
                'due_ack' => true,
                'status' => DeadlineStatus::Confirmed,
                'confirmed_by' => $user->id,
                'confirmed_at' => Clock::now(),
            ]);

            $this->regenerateReminders($user, $deadline);

            $matter = $deadline->matter;

            if ($oldJurisdiction !== $deadline->jurisdiction) {
                Audit::record($matter, 'deadline.jurisdiction_changed', __('audit.jurisdiction_changed', [
                    'old' => $oldJurisdiction->label(),
                    'new' => $deadline->jurisdiction->label(),
                ]), $deadline, ['jurisdiction' => $oldJurisdiction->value], ['jurisdiction' => $deadline->jurisdiction->value]);
            }

            $description = __('audit.deadline_confirmed', [
                'date' => Format::date($due),
                'rule' => $deadline->basis,
                'start' => Format::date($start),
            ]);

            if ($manual) {
                $description .= ' '.__('audit.deadline_manual');
            }

            if ($wasConfirmed && $old['due_date'] !== $due->toDateString()) {
                $description .= ' '.__('audit.deadline_changed', [
                    'old' => Format::date($old['due_date']),
                    'new' => Format::date($due),
                ]);
            }

            Audit::record($matter, 'deadline.confirmed', $description, $deadline, $old, [
                'status' => 'confirmed',
                'jurisdiction' => $deadline->jurisdiction->value,
                'start_date' => $start->toDateString(),
                'due_date' => $due->toDateString(),
                'due_date_manually_changed' => $manual,
                'remind_3_days' => $deadline->remind_3_days,
                'remind_1_day' => $deadline->remind_1_day,
            ]);

            return $deadline;
        });
    }

    /**
     * Reminders 3 days and 1 day before, each optional. Re-confirming with a
     * new date replaces the earlier ones.
     */
    private function regenerateReminders(User $user, Deadline $deadline): void
    {
        $deadline->reminders()
            ->whereIn('kind', [ReminderKind::DeadlineThreeDays, ReminderKind::DeadlineOneDay])
            ->delete();

        $due = CarbonImmutable::parse($deadline->due_date->toDateString());
        $subject = mb_strtolower(mb_substr($deadline->title, 0, 1)).mb_substr($deadline->title, 1);

        $create = fn (ReminderKind $kind, int $daysBefore, string $text) => Reminder::query()->create([
            'office_id' => $deadline->office_id,
            'matter_id' => $deadline->matter_id,
            'remind_on' => $due->subDays($daysBefore)->toDateString(),
            'text' => $text,
            'detail' => __('deadlines.reminder_detail', ['date' => Format::date($due)]),
            'kind' => $kind,
            'source_type' => $deadline->getMorphClass(),
            'source_id' => $deadline->id,
            'created_by' => $user->id,
        ]);

        if ($deadline->remind_3_days) {
            $create(ReminderKind::DeadlineThreeDays, 3, __('deadlines.reminder_3', ['title' => $subject]));
        }

        if ($deadline->remind_1_day) {
            $create(ReminderKind::DeadlineOneDay, 1, __('deadlines.reminder_1', ['title' => $subject]));
        }
    }
}
