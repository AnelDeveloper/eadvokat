<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/** The page an office sees after its trial has ended. */
class TrialController extends Controller
{
    public function __invoke(): Response|RedirectResponse
    {
        $office = $this->office();

        if (! $office->trialExpired()) {
            return to_route('today');
        }

        return Inertia::render('auth/TrialExpired', [
            'office' => $office->name,
            'endedOn' => $office->trial_ends_on?->format('d.m.Y.'),
        ]);
    }
}
