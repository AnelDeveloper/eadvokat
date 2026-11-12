<?php

use App\Enums\DeadlineStatus;
use App\Models\Deadline;
use App\Models\Document;
use App\Models\MailEntry;
use App\Models\Matter;
use App\Models\Office;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function registerOffice(array $overrides = []): void
{
    test()->post('/registracija', [
        'office_name' => 'Advokatska kancelarija Hadžić',
        'title' => 'adv.',
        'name' => 'Amila Hadžić',
        'email' => 'Amila@Example.test',
        'password' => 'sigurna-lozinka-2026',
        'password_confirmation' => 'sigurna-lozinka-2026',
        ...$overrides,
    ]);
}

function newMatter(array $overrides = []): array
{
    return [
        'case_number' => '65 0 P 123456 26 P',
        'title' => 'Begić protiv Primjer d.o.o.',
        'subject' => 'Radi naknade štete',
        'court' => 'Općinski sud u Sarajevu',
        'represented_side' => 'tužioca',
        'client_name' => 'Emir Begić',
        'jurisdiction' => 'fbih',
        ...$overrides,
    ];
}

it('lets a law office register itself and signs the owner in', function () {
    $this->get('/registracija')->assertOk()->assertInertia(fn ($page) => $page->component('auth/Register'));

    registerOffice();

    $user = User::query()->where('email', 'amila@example.test')->firstOrFail();
    expect($user->office->name)->toBe('Advokatska kancelarija Hadžić')
        ->and($user->office->is_demo)->toBeFalse()
        ->and($user->displayName())->toBe('adv. Amila Hadžić');
    $this->assertAuthenticatedAs($user);
});

it('rejects a duplicate e-mail and a mismatched password', function () {
    User::factory()->create(['email' => 'amila@example.test']);

    registerOffice(['password_confirmation' => 'nesto-drugo']);

    expect(Office::query()->count())->toBe(1);
    $this->assertGuest();
});

it('keeps a new office empty and separate from others', function () {
    demoOffice();
    auth()->logout();
    registerOffice();

    $this->get('/predmeti')->assertInertia(fn ($page) => $page->has('matters', 0));
});

it('opens a matter with an empty cost sheet and an audit entry', function () {
    registerOffice();

    $this->post('/predmeti', newMatter())->assertRedirect();

    $matter = Matter::query()->firstOrFail();
    expect($matter->costSheet)->not->toBeNull()
        ->and($matter->auditLogs()->first()->description)->toBe('Predmet 65 0 P 123456 26 P otvoren.');

    $this->post('/predmeti', newMatter())->assertSessionHasErrors('case_number');
});

it('gives real offices no invented document data', function () {
    Storage::fake('local');
    registerOffice();
    $this->post('/predmeti', newMatter());

    $this->post('/posta/fotografisi', ['file' => UploadedFile::fake()->image('dopis.jpg')]);
    $document = Document::query()->firstOrFail();

    $this->get("/posta/pregled/{$document->id}")->assertInertia(fn ($page) => $page
        ->where('fields.court', '')
        ->where('fields.case_number', '')
        ->where('quotes', [])
        ->where('paragraphs', [])
        ->where('caseNumbers', ['65 0 P 123456 26 P'])
        ->where('isImage', true));

    $matter = Matter::query()->firstOrFail();
    $this->post("/posta/pregled/{$document->id}", [
        'court' => 'Općinski sud u Sarajevu',
        'case_number' => '65 0 P 123456 26 P',
        'doc_type' => 'Rješenje',
        'document_date' => '2026-11-06',
        'received_on' => '2026-11-12',
    ])->assertRedirect('/predmeti/'.$matter->id.'/rokovi/novi?unos='.MailEntry::query()->firstOrFail()->id);

    expect(Deadline::query()->count())->toBe(0);
});

it('enters a deadline by hand as a proposal linked to its source', function () {
    Storage::fake('local');
    registerOffice();
    $this->post('/predmeti', newMatter());
    $matter = Matter::query()->firstOrFail();

    $this->post("/predmeti/{$matter->id}/rokovi", [
        'title' => 'Žalba na presudu',
        'rule_text' => 'Rok iz pouke o pravnom lijeku: 30 dana od dostave',
        'days' => 30,
        'start_date' => '2026-11-12',
        'due_date' => '2026-12-12',
        'source_quote' => 'Protiv ove presude dozvoljena je žalba u roku od 30 dana.',
    ])->assertRedirect();

    $deadline = Deadline::query()->firstOrFail();
    expect($deadline->status)->toBe(DeadlineStatus::Proposed)
        ->and($deadline->due_date_manually_changed)->toBeFalse()
        ->and($deadline->reminders()->count())->toBe(0);
});

it('does not offer invented dictation to real offices', function () {
    registerOffice();

    $this->get('/diktat')->assertInertia(fn ($page) => $page->where('available', false));
});

it('registers over the API and returns a token', function () {
    $this->postJson('/api/v1/auth/register', [
        'office_name' => 'Kancelarija Test',
        'name' => 'Test Advokat',
        'email' => 'test@example.test',
        'password' => 'sigurna-lozinka-2026',
        'password_confirmation' => 'sigurna-lozinka-2026',
        'device_name' => 'iPhone',
    ])->assertOk()->assertJsonStructure(['token', 'user' => ['id', 'displayName']]);
});
