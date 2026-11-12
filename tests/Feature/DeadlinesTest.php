<?php

use App\Actions\ConfirmDeadline;
use App\Enums\DeadlineStatus;
use App\Enums\Jurisdiction;
use App\Enums\ReminderKind;
use App\Models\AuditLog;
use App\Models\Deadline;
use App\Models\Matter;
use App\Models\Reminder;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

function proposedDeadline(Matter $matter): Deadline
{
    return Deadline::query()->create([
        'office_id' => $matter->office_id,
        'matter_id' => $matter->id,
        'title' => 'Izjašnjenje na nalaz i mišljenje vještaka',
        'rule_text' => 'Rok određen u samom dokumentu: 15 dana od dana prijema',
        'days' => 15,
        'basis' => '15 dana od prijema',
        'jurisdiction' => 'fbih',
        'source_quote' => 'Poziva se tužilac da se u roku od 15 dana od dana prijema ovog rješenja pismeno izjasni na nalaz i mišljenje vještaka.',
        'source_highlight' => 'u roku od 15 dana od dana prijema ovog rješenja',
        'start_date' => '2026-11-11',
        'due_date' => '2026-11-26',
        'status' => DeadlineStatus::Proposed,
    ]);
}

function confirmation(array $overrides = []): array
{
    return [
        'jurisdiction' => 'fbih',
        'start_date' => '2026-11-11',
        'due_date' => '2026-11-26',
        'rule_ack' => true,
        'start_ack' => true,
        'due_ack' => true,
        'remind_3_days' => true,
        'remind_1_day' => true,
        ...$overrides,
    ];
}

it('shows a proposal with its source and assumptions', function () {
    [$user, $matter] = demoOffice();
    $deadline = proposedDeadline($matter);

    $this->actingAs($user)->get("/rokovi/{$deadline->id}/prijedlog")
        ->assertInertia(fn ($page) => $page->component('flow/DeadlineProposal')
            ->where('deadline.days', 15)
            ->where('deadline.highlight', 'u roku od 15 dana od dana prijema ovog rješenja')
            ->where('deadline.isConfirmed', false)
            ->has('jurisdictions', 3));
});

it('confirms a deadline with all three acknowledgements and sets reminders', function () {
    [$user, $matter] = demoOffice();
    $deadline = proposedDeadline($matter);

    $this->actingAs($user)->post("/rokovi/{$deadline->id}/potvrda", confirmation())
        ->assertRedirect("/predmeti/{$matter->id}");

    $deadline->refresh();
    expect($deadline->status)->toBe(DeadlineStatus::Confirmed)
        ->and($deadline->confirmed_by)->toBe($user->id)
        ->and($deadline->confirmed_at->format('Y-m-d H:i'))->toBe('2026-11-11 08:05')
        ->and($deadline->due_date_manually_changed)->toBeFalse();

    expect($deadline->reminders()->orderBy('remind_on')->get()->map(fn ($r) => [$r->remind_on->toDateString(), $r->text])->all())
        ->toBe([
            ['2026-11-23', 'Za 3 dana ističe rok: izjašnjenje na nalaz i mišljenje vještaka'],
            ['2026-11-25', 'Sutra ističe rok: izjašnjenje na nalaz i mišljenje vještaka'],
        ]);

    $audit = AuditLog::query()->where('action', 'deadline.confirmed')->latest('id')->firstOrFail();
    expect($audit->description)->toBe('Rok 26.11.2026. potvrdio advokat. Pravilo: 15 dana od prijema; početak 11.11.2026.')
        ->and($audit->old_values['status'])->toBe('proposed')
        ->and($audit->new_values['due_date'])->toBe('2026-11-26');
});

it('refuses confirmation with only two acknowledgements and changes nothing', function (string $missing) {
    [$user, $matter] = demoOffice();
    $deadline = proposedDeadline($matter);
    $auditCount = AuditLog::query()->count();

    $this->actingAs($user)
        ->post("/rokovi/{$deadline->id}/potvrda", confirmation([$missing => false]))
        ->assertSessionHasErrors($missing);

    $deadline->refresh();
    expect($deadline->status)->toBe(DeadlineStatus::Proposed)
        ->and($deadline->confirmed_by)->toBeNull()
        ->and($deadline->rule_ack || $deadline->start_ack || $deadline->due_ack)->toBeFalse()
        ->and(Reminder::query()->where('source_id', $deadline->id)->where('source_type', $deadline->getMorphClass())->count())->toBe(0)
        ->and(AuditLog::query()->count())->toBe($auditCount);
})->with(['rule_ack', 'start_ack', 'due_ack']);

it('enforces the three acknowledgements in the domain action too', function () {
    [$user, $matter] = demoOffice();
    $deadline = proposedDeadline($matter);

    expect(fn () => app(ConfirmDeadline::class)->handle($user, $deadline, [
        'jurisdiction' => Jurisdiction::Fbih,
        'start_date' => '2026-11-11',
        'due_date' => '2026-11-26',
        'rule_ack' => true,
        'start_ack' => true,
        'due_ack' => false,
        'remind_3_days' => true,
        'remind_1_day' => true,
    ]))->toThrow(ValidationException::class);

    expect($deadline->fresh()->status)->toBe(DeadlineStatus::Proposed);
});

it('backs the rule with a database check constraint', function () {
    [$user, $matter] = demoOffice();
    $deadline = proposedDeadline($matter);

    expect(fn () => Deadline::query()->whereKey($deadline->id)->update([
        'status' => 'confirmed',
        'rule_ack' => true,
        'start_ack' => true,
        'due_ack' => false,
        'confirmed_by' => $user->id,
        'confirmed_at' => now(),
    ]))->toThrow(QueryException::class, 'deadlines_acknowledged_check');
});

it('flags a manually changed last day and records it', function () {
    [$user, $matter] = demoOffice();
    $deadline = proposedDeadline($matter);

    $this->actingAs($user)->post("/rokovi/{$deadline->id}/potvrda", confirmation(['due_date' => '2026-11-27']));

    expect($deadline->fresh()->due_date_manually_changed)->toBeTrue()
        ->and(AuditLog::query()->latest('id')->first()->description)->toEndWith('Posljednji dan izmijenjen ručno.');
});

it('creates only the reminders the lawyer kept', function () {
    [$user, $matter] = demoOffice();
    $deadline = proposedDeadline($matter);

    $this->actingAs($user)->post("/rokovi/{$deadline->id}/potvrda", confirmation(['remind_3_days' => false]));

    expect($deadline->reminders()->pluck('kind')->all())->toBe([ReminderKind::DeadlineOneDay]);
});

it('regenerates reminders when re-confirmed with a new date', function () {
    [$user, $matter] = demoOffice();
    $deadline = proposedDeadline($matter);
    $this->actingAs($user)->post("/rokovi/{$deadline->id}/potvrda", confirmation());

    $this->actingAs($user)->post("/rokovi/{$deadline->id}/potvrda", confirmation(['start_date' => '2026-11-12', 'due_date' => '2026-11-27']));

    expect($deadline->reminders()->orderBy('remind_on')->pluck('remind_on')->map->toDateString()->all())
        ->toBe(['2026-11-24', '2026-11-26'])
        ->and(AuditLog::query()->latest('id')->first()->description)->toEndWith('Raniji posljednji dan: 26.11.2026. → 27.11.2026.');
});

it('records a jurisdiction change with old and new values', function () {
    [$user, $matter] = demoOffice();
    $deadline = proposedDeadline($matter);

    $this->actingAs($user)->post("/rokovi/{$deadline->id}/potvrda", confirmation(['jurisdiction' => 'rs']));

    $log = AuditLog::query()->where('action', 'deadline.jurisdiction_changed')->firstOrFail();
    expect($log->description)->toBe('Nadležnost promijenjena: FBiH → Republika Srpska.')
        ->and($log->old_values)->toBe(['jurisdiction' => 'fbih'])
        ->and($log->new_values)->toBe(['jurisdiction' => 'rs']);
});

it('does not let another office confirm a deadline', function () {
    [, $matter] = demoOffice();
    $deadline = proposedDeadline($matter);

    $this->actingAs(User::factory()->create())
        ->post("/rokovi/{$deadline->id}/potvrda", confirmation())
        ->assertNotFound();

    expect($deadline->fresh()->status)->toBe(DeadlineStatus::Proposed);
});

it('builds the Danas briefing as in the prototype', function () {
    [$user] = demoOffice();

    $this->actingAs($user)->get('/danas')
        ->assertInertia(fn ($page) => $page->component('Today')
            ->where('date', 'srijeda, 11. novembar 2026.')
            ->where('lawyer', 'adv. Lejla Karić')
            ->where('groups.0.key', 'hearings')
            ->where('groups.0.rows.0.when', '11:00')
            ->where('groups.0.rows.0.title', 'Nastavak glavne rasprave')
            ->where('groups.1.rows.0.when', '13.11.')
            ->where('groups.1.rows.0.status', 'urgent')
            ->where('groups.2.rows.0.when', 'danas')
            ->where('groups.3.key', 'review')
            ->where('groups.3.rows.0.title', 'Troškovnik: 1 stavka čeka pregled')
            ->where('groups.4.key', 'tomorrow')
            ->where('groups.4.rows.0.title', 'Sastanak sa strankom A. Selimović'));
});

it('lists proposals under Čeka vaš pregled but never as reminders', function () {
    [$user, $matter] = demoOffice();
    proposedDeadline($matter);

    $this->actingAs($user)->get('/danas')
        ->assertInertia(fn ($page) => $page
            ->where('groups.3.rows.0.title', 'Izjašnjenje na nalaz i mišljenje vještaka')
            ->where('groups.3.rows.0.status', 'proposed'));

    expect(Reminder::query()->where('kind', '!=', 'manual')->count())->toBe(0);
});

it('toggles today\'s court check', function () {
    [$user] = demoOffice();
    $reminder = Reminder::query()->firstOrFail();

    $this->actingAs($user)->post("/podsjetnici/{$reminder->id}/provjereno")->assertRedirect();
    expect($reminder->fresh()->checked_at)->not->toBeNull();

    $this->actingAs($user)->get('/danas')
        ->assertInertia(fn ($page) => $page->where('groups.2.rows.0.sub', 'Označeno kao provjereno')
            ->where('groups.2.rows.0.status', 'recorded'));
});

it('marks the calendar with the right markers', function () {
    [$user, $matter] = demoOffice();
    $deadline = proposedDeadline($matter);

    $this->actingAs($user)->get('/kalendar')
        ->assertInertia(fn ($page) => $page->component('Calendar')
            ->where('monthLabel', 'Novembar 2026.')
            ->where('offset', 6)
            ->where('selectedDay', 11)
            ->where('selectedLabel', 'srijeda, 11.11.2026.')
            ->where('days.10.markers', ['muted', 'event'])
            ->where('days.12.markers', ['urgent'])
            ->where('days.25.markers', ['proposed'])
            ->has('rows', 2));

    $this->actingAs($user)->post("/rokovi/{$deadline->id}/potvrda", confirmation());

    $this->actingAs($user)->get('/kalendar?mjesec=2026-11&dan=26')
        ->assertInertia(fn ($page) => $page
            ->where('days.22.markers', ['muted'])
            ->where('days.25.markers', ['deadline'])
            ->where('rows.0.title', 'Izjašnjenje na nalaz i mišljenje vještaka'));

    $this->actingAs($user)->get('/kalendar?mjesec=2026-12')
        ->assertInertia(fn ($page) => $page->where('monthLabel', 'Decembar 2026.')->where('offset', 1));
});
