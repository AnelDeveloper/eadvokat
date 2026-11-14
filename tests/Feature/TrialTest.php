<?php

use App\Models\Office;
use App\Models\User;
use Carbon\CarbonImmutable;

afterEach(fn () => CarbonImmutable::setTestNow());

function registerTrialOffice(): User
{
    test()->post('/registracija', [
        'office_name' => 'Kancelarija Proba',
        'title' => 'adv.',
        'name' => 'Proba Advokat',
        'email' => 'proba@example.test',
        'password' => 'tajna-lozinka-123',
        'password_confirmation' => 'tajna-lozinka-123',
    ])->assertRedirect();

    return User::query()->where('email', 'proba@example.test')->firstOrFail();
}

it('gives a newly registered office ten days of access, today included', function () {
    CarbonImmutable::setTestNow('2026-11-14 10:00:00');
    $user = registerTrialOffice();

    expect($user->office->trial_ends_on->toDateString())->toBe('2026-11-23')
        ->and($user->office->trialDaysLeft())->toBe(10);

    $this->get('/danas')->assertOk()
        ->assertInertia(fn ($page) => $page->where('auth.user.trialDaysLeft', 10));
});

it('keeps the app open on the last trial day and closes it the day after', function () {
    CarbonImmutable::setTestNow('2026-11-14 10:00:00');
    $user = registerTrialOffice();

    CarbonImmutable::setTestNow('2026-11-23 23:30:00');
    $this->actingAs($user)->get('/danas')->assertOk();

    CarbonImmutable::setTestNow('2026-11-24 00:10:00');
    $this->actingAs($user)->get('/danas')->assertRedirect('/probni-period');
    $this->actingAs($user)->get('/probni-period')->assertOk()
        ->assertInertia(fn ($page) => $page->component('auth/TrialExpired')->where('endedOn', '23.11.2026.'));
    $this->actingAs($user)->getJson('/api/v1/today')->assertForbidden()->assertJson(['code' => 'trial_expired']);

    // Data stays; logging out still works.
    expect(Office::query()->find($user->office_id))->not->toBeNull();
    $this->actingAs($user)->post('/logout')->assertRedirect();
});

it('does not limit offices without a trial', function () {
    $user = User::factory()->create();

    expect($user->office->trial_ends_on)->toBeNull();
    $this->actingAs($user)->get('/danas')->assertOk()
        ->assertInertia(fn ($page) => $page->where('auth.user.trialDaysLeft', null));
    $this->actingAs($user)->get('/probni-period')->assertRedirect('/danas');
});

it('extends or removes a trial from the command line', function () {
    CarbonImmutable::setTestNow('2026-11-14 10:00:00');
    $user = registerTrialOffice();

    $this->artisan('office:trial', ['email' => 'proba@example.test', 'days' => 30])->assertSuccessful();
    expect($user->office->fresh()->trial_ends_on->toDateString())->toBe('2026-12-13');

    $this->artisan('office:trial', ['email' => 'proba@example.test', '--unlimited' => true])->assertSuccessful();
    expect($user->office->fresh()->trial_ends_on)->toBeNull();

    $this->artisan('office:trial', ['email' => 'nema@example.test'])->assertFailed();
});
