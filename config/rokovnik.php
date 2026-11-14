<?php

return [
    // Dev-only: pins "today" so the seeded demo scenario renders as in the
    // prototype. Ignored outside the local environment.
    'demo_today' => env('DEMO_TODAY'),

    // Days of access a newly registered office gets before the trial ends.
    'trial_days' => (int) env('TRIAL_DAYS', 10),
];
