<?php

use App\Models\Deadline;
use App\Models\Document;
use App\Models\Matter;
use App\Models\Office;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(fn () => $this->travelTo(CarbonImmutable::parse('2026-11-12 09:30', 'Europe/Sarajevo')));

it('gives every visitor a private, temporary demo office', function () {
    $this->get('/demo')->assertOk()->assertInertia(fn ($page) => $page->component('Demo')
        ->where('blocked', false)
        ->where('progress', array_fill(0, 9, false)));

    $user = auth()->user();
    expect($user->office->is_demo)->toBeTrue()
        ->and($user->office->pinned_today->toDateString())->toBe('2026-11-11')
        ->and($user->office->expires_at->toDateTimeString())->toBe(now()->addHours(24)->toDateTimeString())
        ->and(Matter::query()->count())->toBe(1);
});

it('pins the demo office to the scripted today', function () {
    $this->get('/demo');

    $this->get('/danas')->assertInertia(fn ($page) => $page->where('date', 'srijeda, 11. novembar 2026.'));
});

it('keeps two visitors apart', function () {
    $this->get('/demo');
    $first = auth()->user();
    auth()->logout();
    $this->flushSession();

    $this->get('/demo');
    $second = auth()->user();

    expect($second->office_id)->not->toBe($first->office_id);

    $firstMatter = Matter::query()->withoutGlobalScopes()->where('office_id', $first->office_id)->firstOrFail();
    $this->get("/predmeti/{$firstMatter->id}")->assertNotFound();
});

it('resets the demo to a fresh office', function () {
    $this->get('/demo');
    $old = auth()->user()->office_id;

    $this->post('/demo/reset')->assertRedirect('/demo');

    expect(auth()->user()->office_id)->not->toBe($old)
        ->and(Office::query()->find($old))->toBeNull();
});

it('tracks guide progress', function () {
    $this->get('/demo');

    $this->getJson('/demo/napredak')->assertJson(['progress' => array_fill(0, 9, false)]);
});

it('never turns a real user into a demo user', function () {
    $real = User::factory()->create();

    $this->actingAs($real)->get('/demo')->assertInertia(fn ($page) => $page->where('blocked', true));
    $this->actingAs($real)->post('/demo/reset');

    expect(auth()->id())->toBe($real->id)
        ->and(Office::query()->where('is_demo', true)->count())->toBe(0);
});

it('prunes demo offices after 24 hours, but never real ones', function () {
    $this->get('/demo');
    $demoOffice = auth()->user()->office_id;
    $real = Office::factory()->create(['expires_at' => now()->subDay()]);

    $this->travel(25)->hours();
    $this->artisan('demo:prune')->assertSuccessful();

    expect(Office::query()->find($demoOffice))->toBeNull()
        ->and(Office::query()->find($real->id))->not->toBeNull();
});

it('points the landing page to the live demo', function () {
    $this->get('/')->assertOk();
    expect(file_get_contents(resource_path('js/pages/Landing.vue')))->toContain('const prototypeHref = `${appOrigin}/demo`;');
});

it('ticks guide steps as the visitor works through the scenario', function () {
    Storage::fake('local');
    $this->get('/demo');

    $this->post('/posta/fotografisi', ['file' => UploadedFile::fake()->image('r.jpg')]);
    $document = Document::query()->whereNotNull('path')->latest('id')->firstOrFail();
    $this->post("/posta/pregled/{$document->id}", [
        'court' => 'Općinski sud u Sarajevu',
        'case_number' => '65 0 P 084512 25 P',
        'doc_type' => 'Rješenje — dostava nalaza vještaka',
        'document_date' => '2026-11-06',
        'received_on' => '2026-11-11',
    ]);
    $deadline = Deadline::query()->latest('id')->firstOrFail();
    $this->post("/rokovi/{$deadline->id}/potvrda", [
        'jurisdiction' => 'fbih', 'start_date' => '2026-11-11', 'due_date' => '2026-11-26',
        'rule_ack' => true, 'start_ack' => true, 'due_ack' => true, 'remind_3_days' => true, 'remind_1_day' => true,
    ]);
    $this->get('/kalendar');

    $this->getJson('/demo/napredak')->assertJson(['progress' => [true, true, true, true, true, false, false, false, false]]);
});
