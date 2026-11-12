<?php

namespace App\Http\Controllers;

use App\Actions\ConfirmEvent;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class EventController extends Controller
{
    public function confirm(Request $request, Event $event, ConfirmEvent $action): RedirectResponse
    {
        Gate::authorize('update', $event);

        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'time' => ['required', 'date_format:H:i'],
            'location' => ['nullable', 'string', 'max:20'],
        ], attributes: ['date' => 'datum', 'time' => 'vrijeme']);

        $action->handle($this->user(), $event, [
            'date' => $validated['date'],
            'time' => $validated['time'],
            'location' => ($validated['location'] ?? '') !== '' ? $validated['location'] : null,
        ]);

        Inertia::flash('toast', __('dictation.event_confirmed'));

        return back();
    }
}
