<?php

use App\Models\Document;
use App\Models\Matter;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('lists the matters of the user office', function () {
    [$user] = demoOffice();

    $this->actingAs($user)->get('/predmeti')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('matters/Index')
            ->has('matters', 1)
            ->where('matters.0.caseNumber', '65 0 P 084512 25 P')
            ->where('matters.0.meta', 'Radi isplate duga · Općinski sud u Sarajevu · zastupamo tužioca')
            ->where('matters.0.next', 'Nastavak glavne rasprave · 11.11.2026.')
            ->where('matters.0.costLine', '1 stavka čeka pregled'));
});

it('shows the timeline split into upcoming and recorded', function () {
    [$user, $matter] = demoOffice();

    $this->actingAs($user)->get("/predmeti/{$matter->id}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('matters/Timeline')
            ->where('matter.meta', 'Radi isplate duga · Općinski sud u Sarajevu · FBiH · zastupamo tužioca (Amra Selimović)')
            ->where('upcoming.0.title', 'Provjeriti u pisarnici je li vraćena dostavnica za svjedoka')
            ->where('upcoming.1.title', 'Nastavak glavne rasprave')
            ->where('upcoming.1.time', '11:00')
            ->where('upcoming.3.title', 'Izjašnjenje na podnesak tuženog')
            ->where('upcoming.3.status', 'urgent')
            ->where('upcoming.3.sub', 'petak · za 2 dana · 15 dana od prijema 29.10.2026.')
            ->has('upcoming', 4)
            ->where('recorded.0.title', 'Uvid u spis na pisarnici')
            ->where('recorded.0.type', 'Posjeta sudu')
            ->where('recorded.1.title', 'Zaprimljen podnesak tuženog')
            ->where('recorded.1.status', 'recorded')
            ->has('recorded', 6));
});

it('shows the audit log newest first', function () {
    [$user, $matter] = demoOffice();

    $this->actingAs($user)->get("/predmeti/{$matter->id}/izmjene")
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('matters/Audit')
            ->where('entries.0.at', '09.11.2026. 13:20')
            ->where('entries.0.user', 'L. Karić')
            ->where('entries.2.description', 'Unos U-2026/038 sačuvan u knjigu pošte nakon pregleda.'));
});

it('does not let a user from another office see a matter', function () {
    [, $matter] = demoOffice();
    $stranger = User::factory()->create();

    $this->actingAs($stranger)->get("/predmeti/{$matter->id}")->assertNotFound();
    $this->actingAs($stranger)->get("/predmeti/{$matter->id}/izmjene")->assertNotFound();
    $this->actingAs($stranger)->get('/predmeti')
        ->assertInertia(fn ($page) => $page->has('matters', 0));
});

it('scopes every model query to the current office', function () {
    [, $matter] = demoOffice();
    $stranger = User::factory()->create();

    $this->actingAs($stranger);

    expect(Matter::query()->count())->toBe(0)
        ->and(Document::query()->count())->toBe(0)
        ->and(Matter::query()->find($matter->id))->toBeNull();
});

it('denies the policy check even when the office scope is bypassed', function () {
    [, $matter] = demoOffice();
    $stranger = User::factory()->create();

    $unscoped = Matter::query()->withoutGlobalScopes()->findOrFail($matter->id);

    expect($stranger->can('view', $unscoped))->toBeFalse();
});

it('renders a source document', function () {
    [$user] = demoOffice();
    $document = Document::query()->where('name', 'Podnesak tuženog · U-2026/038')->firstOrFail();

    $this->actingAs($user)->get("/dokumenti/{$document->id}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('flow/DocumentViewer')
            ->where('document.isFictional', true)
            ->where('document.paragraphs.2.text', 'PODNESAK TUŽENOG'));
});

it('serves uploaded files only to the owning office', function () {
    Storage::fake('local');
    [$user, $matter] = demoOffice();
    $path = UploadedFile::fake()->image('rjesenje.jpg')->store('documents', 'local');
    $document = Document::query()->create([
        'office_id' => $user->office_id,
        'matter_id' => $matter->id,
        'kind' => 'court_document',
        'name' => 'Rješenje',
        'disk' => 'local',
        'path' => $path,
        'mime_type' => 'image/jpeg',
    ]);

    $this->actingAs($user)->get("/dokumenti/{$document->id}/datoteka")->assertOk();
    $this->actingAs(User::factory()->create())->get("/dokumenti/{$document->id}/datoteka")->assertNotFound();
    auth()->logout();
    $this->get("/dokumenti/{$document->id}/datoteka")->assertRedirect('/login');
});
