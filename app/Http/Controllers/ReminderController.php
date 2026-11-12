<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use App\Support\Clock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ReminderController extends Controller
{
    public function toggle(Reminder $reminder): RedirectResponse
    {
        Gate::authorize('update', $reminder);

        $reminder->update($reminder->checked_at === null
            ? ['checked_at' => Clock::now(), 'checked_by' => $this->user()->id]
            : ['checked_at' => null, 'checked_by' => null]);

        return back();
    }
}
