<?php

use App\Enums\DeadlineStatus;
use App\Models\AuditLog;
use App\Models\Deadline;
use App\Models\Document;
use App\Models\MailEntry;
use App\Models\Reminder;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(fn () => Storage::fake('local'));

function captureDocument(User $user): Document
{
    test()->actingAs($user)
        ->post('/posta/fotografisi', ['file' => UploadedFile::fake()->image('rjesenje.jpg')])
        ->assertRedirect();

    return Document::query()->latest('id')->firstOrFail();
}

function reviewedFields(array $overrides = []): array
{
    return [
        'court' => 'Općinski sud u Sarajevu',
        'case_number' => '65 0 P 084512 25 P',
        'doc_type' => 'Rješenje — dostava nalaza vještaka',
        'document_date' => '2026-11-06',
        'received_on' => '2026-11-11',
        ...$overrides,
    ];
}

it('lists incoming and outgoing registers with their links', function () {
    [$user] = demoOffice();

    $this->actingAs($user)->get('/posta')
        ->assertInertia(fn ($page) => $page->component('mail/Index')
            ->where('direction', 'in')
            ->has('entries', 2)
            ->where('entries.0.number', 'U-2026/038')
            ->where('entries.0.date', '29.10.2026.')
            ->where('entries.0.link', 'Povezan rok 13.11.2026. — potvrdio advokat')
            ->where('entries.0.linkTone', 'confirmed')
            ->where('entries.1.link', null));

    $this->actingAs($user)->get('/posta?smjer=izlazna')
        ->assertInertia(fn ($page) => $page->where('direction', 'out')
            ->where('entries.0.number', 'I-2025/044')
            ->where('entries.0.sub', 'Preporučeno poštom · 65 0 P 084512 25 P')
            ->where('entries.0.link', 'Dokaz o predaji: potvrda o prijemu pošiljke'));
});

it('stores a captured document privately with the extractor proposal', function () {
    [$user] = demoOffice();
    $document = captureDocument($user);

    Storage::disk('local')->assertExists($document->path);
    expect($document->path)->toStartWith("documents/{$user->office_id}/")
        ->and($document->extraction['fields']['received_on'])->toBe('2026-11-11')
        ->and(MailEntry::query()->count())->toBe(3);

    $this->actingAs($user)->get("/posta/pregled/{$document->id}")
        ->assertInertia(fn ($page) => $page->component('flow/MailReview')
            ->where('registerNumber', 'U-2026/041')
            ->where('receivedOnAssumed', true)
            ->where('fields.doc_type', 'Rješenje — dostava nalaza vještaka')
            ->where('quotes.1.text', 'Protiv ovog rješenja nije dozvoljena posebna žalba.'));
});

it('rejects files that are not images or PDFs', function () {
    [$user] = demoOffice();

    $this->actingAs($user)
        ->post('/posta/fotografisi', ['file' => UploadedFile::fake()->create('x.exe', 10, 'application/x-msdownload')])
        ->assertSessionHasErrors('file');
});

it('saves the entry and proposes — but does not activate — a deadline', function () {
    [$user, $matter] = demoOffice();
    $document = captureDocument($user);

    $this->actingAs($user)->post("/posta/pregled/{$document->id}", reviewedFields())->assertRedirect();

    $entry = MailEntry::query()->where('document_id', $document->id)->firstOrFail();
    expect($entry->register_number)->toBe('U-2026/041')
        ->and($entry->confirmed_by)->toBe($user->id)
        ->and($document->fresh()->name)->toBe('Rješenje · U-2026/041')
        ->and($document->fresh()->matter_id)->toBe($matter->id);

    $deadline = Deadline::query()->where('source_mail_entry_id', $entry->id)->firstOrFail();
    expect($deadline->status)->toBe(DeadlineStatus::Proposed)
        ->and($deadline->start_date->toDateString())->toBe('2026-11-11')
        ->and($deadline->due_date->toDateString())->toBe('2026-11-26')
        ->and($deadline->source_document_id)->toBe($document->id)
        ->and($deadline->source_quote)->toContain('u roku od 15 dana')
        ->and($deadline->rule_ack || $deadline->start_ack || $deadline->due_ack)->toBeFalse();

    // Rule 1: a proposal never creates reminders.
    expect(Reminder::query()->where('source_type', $deadline->getMorphClass())->count())->toBe(0);

    $audit = AuditLog::query()->where('action', 'mail_entry.saved')->latest('id')->firstOrFail();
    expect($audit->description)->toBe('Unos U-2026/041 sačuvan u knjigu pošte. Polja pregledao advokat.')
        ->and($audit->new_values['register_number'])->toBe('U-2026/041');
});

it('uses the corrected receipt date for the proposal', function () {
    [$user] = demoOffice();
    $document = captureDocument($user);

    $this->actingAs($user)->post("/posta/pregled/{$document->id}", reviewedFields(['received_on' => '2026-11-10']));

    expect(Deadline::query()->latest('id')->first()->due_date->toDateString())->toBe('2026-11-25');
});

it('numbers entries sequentially and never twice for the same document', function () {
    [$user] = demoOffice();
    $first = captureDocument($user);
    $this->actingAs($user)->post("/posta/pregled/{$first->id}", reviewedFields());
    $this->actingAs($user)->post("/posta/pregled/{$first->id}", reviewedFields())->assertRedirect('/posta');

    $second = captureDocument($user);
    $this->actingAs($user)->post("/posta/pregled/{$second->id}", reviewedFields());

    expect(MailEntry::query()->where('direction', 'in')->orderBy('register_sequence')->pluck('register_number')->all())
        ->toBe(['U-2026/007', 'U-2026/038', 'U-2026/041', 'U-2026/042']);
});

it('starts a new sequence each year and per office', function () {
    [$user] = demoOffice();
    $document = captureDocument($user);
    $this->actingAs($user)->post("/posta/pregled/{$document->id}", reviewedFields(['received_on' => '2027-01-04']));

    expect(MailEntry::query()->latest('id')->first()->register_number)->toBe('U-2027/001');
});

it('refuses an unknown case number and changes nothing', function () {
    [$user] = demoOffice();
    $document = captureDocument($user);

    $this->actingAs($user)
        ->post("/posta/pregled/{$document->id}", reviewedFields(['case_number' => '99 0 P 000000 26 P']))
        ->assertSessionHasErrors(['case_number' => 'U kancelariji ne postoji predmet s ovim brojem. Prvo ga otvorite u odjeljku Predmeti.']);

    expect(MailEntry::query()->count())->toBe(3)
        ->and(Deadline::query()->count())->toBe(1);
});

it('keeps a captured document private to its office', function () {
    [$user] = demoOffice();
    $document = captureDocument($user);
    $stranger = User::factory()->create();

    $this->actingAs($stranger)->get("/posta/pregled/{$document->id}")->assertNotFound();
    $this->actingAs($stranger)->post("/posta/pregled/{$document->id}", reviewedFields())->assertNotFound();
});
