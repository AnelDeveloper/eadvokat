<?php

namespace App\Actions;

use App\Enums\ItemStatus;
use App\Models\Event;
use App\Models\User;
use App\Support\Audit;
use App\Support\Clock;
use App\Support\Format;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Confirms a proposed calendar event. An uncertain value (e.g. the courtroom)
 * must be resolved first.
 */
final class ConfirmEvent
{
    /**
     * @param  array{date: string, time: string, location: string|null}  $input
     */
    public function handle(User $user, Event $event, array $input): Event
    {
        if ($event->uncertain !== null && $input['location'] === null) {
            throw ValidationException::withMessages(['location' => __('dictation.room_unresolved')]);
        }

        return DB::transaction(function () use ($user, $event, $input) {
            $old = ['starts_at' => $event->starts_at->toDateTimeString(), 'location' => $event->location, 'status' => $event->status->value];
            $startsAt = CarbonImmutable::parse($input['date'].' '.$input['time'], config('app.timezone'));

            $event->update([
                'starts_at' => $startsAt,
                'location' => $input['location'] !== null ? __('dictation.room', ['room' => $input['location']]) : $event->location,
                'uncertain' => null,
                'status' => ItemStatus::Confirmed,
                'confirmed_by' => $user->id,
                'confirmed_at' => Clock::now(),
            ]);

            Audit::record($event->matter, 'event.confirmed', __('audit.event_confirmed', [
                'date' => Format::date($startsAt),
                'time' => $startsAt->format('H:i'),
            ]), $event, $old, ['starts_at' => $startsAt->toDateTimeString(), 'location' => $event->location, 'status' => 'confirmed']);

            return $event;
        });
    }
}
