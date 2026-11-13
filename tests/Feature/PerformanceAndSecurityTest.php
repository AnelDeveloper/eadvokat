<?php

use App\Enums\DeadlineStatus;
use App\Models\Deadline;
use App\Models\Reminder;
use App\Queries\Agenda;
use App\Queries\AgendaItem;
use App\Screens\MailScreens;

it('loads only the requested days plus open proposals', function () {
    [$user, $matter] = demoOffice();
    $this->actingAs($user);

    $old = Reminder::query()->create([
        'matter_id' => $matter->id,
        'remind_on' => '2024-01-10',
        'text' => 'Stari podsjetnik',
        'kind' => 'manual',
    ]);
    $proposal = Deadline::query()->firstOrFail()->replicate();
    $proposal->forceFill([
        'status' => DeadlineStatus::Proposed,
        'start_date' => '2024-01-01',
        'due_date' => '2024-01-16',
        'confirmed_at' => null,
        'confirmed_by' => null,
        'fulfilled_by_mail_entry_id' => null,
    ])->save();

    $agenda = new Agenda;
    $keys = $agenda->items(from: $agenda->today, to: $agenda->today->addDays(3), withProposed: true)
        ->map(fn (AgendaItem $i) => $i->key);

    expect($keys)->not->toContain('reminder:'.$old->id)
        ->and($keys)->toContain('deadline:'.$proposal->id)
        ->and($agenda->items()->map(fn (AgendaItem $i) => $i->key))->toContain('reminder:'.$old->id);
});

it('pages the mail register', function () {
    [$user] = demoOffice();

    $this->actingAs($user)->get('/posta')
        ->assertInertia(fn ($page) => $page->where('hasMore', false)
            ->where('nextLimit', MailScreens::PAGE * 2));
});

it('sends hardening headers and keeps signed-in pages out of caches', function () {
    [$user] = demoOffice();

    $this->get('/')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

    $response = $this->actingAs($user)->get('/danas');
    expect($response->headers->get('Cache-Control'))->toContain('no-store')
        ->and($response->headers->get('Content-Security-Policy'))->toContain("frame-ancestors 'self'");
});
