<?php

namespace App\Actions;

use App\Enums\ItemStatus;
use App\Models\Note;
use App\Models\User;
use App\Support\Audit;
use App\Support\Clock;

final class ConfirmNote
{
    public function handle(User $user, Note $note, string $body): Note
    {
        $old = ['body' => $note->body, 'status' => $note->status->value];

        $note->update([
            'body' => $body,
            'status' => ItemStatus::Confirmed,
            'confirmed_by' => $user->id,
            'confirmed_at' => Clock::now(),
        ]);

        Audit::record($note->matter, 'note.confirmed', __('audit.note_confirmed'), $note, $old, ['body' => $body, 'status' => 'confirmed']);

        return $note;
    }
}
