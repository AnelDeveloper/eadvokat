<?php

use App\Enums\DeadlineStatus;
use App\Enums\ItemStatus;
use App\Enums\ReminderKind;
use App\Models\AuditLog;
use App\Models\CostItem;
use App\Models\CostSheet;
use App\Models\Deadline;
use App\Models\MailEntry;
use App\Models\Matter;
use App\Models\Reminder;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(fn () => Storage::fake('local'));

function submission(Matter $matter, array $overrides = []): array
{
    return [
        'matter_id' => $matter->id,
        'doc_type' => 'Podnesak — dostava adrese svjedoka',
        'sent_on' => '2026-11-11',
        'delivery_method' => 'in_person',
        'proof' => UploadedFile::fake()->image('stambilj.jpg'),
        'fulfils' => [],
        'reminder_text' => 'Za deset dana provjeri u pisarnici šta je s podneskom.',
        'reminder_on' => '2026-11-23',
        ...$overrides,
    ];
}

it('offers the next outgoing number and open deadlines', function () {
    [$user] = demoOffice();

    $this->actingAs($user)->get('/posta/podnesak')
        ->assertInertia(fn ($page) => $page->component('flow/OutgoingSubmission')
            ->where('nextNumber', 'I-2026/029')
            ->where('today', '2026-11-11')
            ->where('matters.0.openDeadlines.0.due', '13.11.2026.'));
});

it('registers the submission with proof and a follow-up reminder', function () {
    [$user, $matter] = demoOffice();

    $this->actingAs($user)->post('/posta/podnesak', submission($matter))
        ->assertRedirect("/predmeti/{$matter->id}");

    $entry = MailEntry::query()->where('direction', 'out')->latest('id')->firstOrFail();
    expect($entry->register_number)->toBe('I-2026/029')
        ->and($entry->proof_document_id)->not->toBeNull()
        ->and($entry->document_id)->toBe($entry->proof_document_id)
        ->and($entry->proof_note)->toBe('fotografija prijemnog štambilja')
        ->and($entry->note)->toBe('lično na pisarnici · dokaz o predaji priložen');

    Storage::disk('local')->assertExists($entry->proofDocument->path);

    $reminder = Reminder::query()->where('kind', ReminderKind::FollowUp)->firstOrFail();
    expect($reminder->remind_on->toDateString())->toBe('2026-11-23')
        ->and($reminder->detail)->toBe('I-2026/029 · postavljeno 11.11.2026.');

    $cost = CostItem::query()->latest('id')->firstOrFail();
    expect($cost->status)->toBe(ItemStatus::Proposed)
        ->and($cost->amount)->toBeNull()
        ->and($cost->source_label)->toBe('pošta I-2026/029');

    expect(AuditLog::query()->where('action', 'mail_entry.outgoing')->first()->description)
        ->toBe('Izlazni podnesak I-2026/029 evidentiran s dokazom o predaji. Podsjetnik postavljen za 23.11.2026.');
});

it('marks a deadline fulfilled only when the lawyer ticks it', function () {
    [$user, $matter] = demoOffice();
    $deadline = Deadline::query()->firstOrFail();

    $this->actingAs($user)->post('/posta/podnesak', submission($matter));
    expect($deadline->fresh()->status)->toBe(DeadlineStatus::Confirmed);

    $this->actingAs($user)->post('/posta/podnesak', submission($matter, ['fulfils' => [$deadline->id]]));

    $deadline->refresh();
    expect($deadline->status)->toBe(DeadlineStatus::Fulfilled)
        ->and($deadline->fulfilledBy->register_number)->toBe('I-2026/030');

    $this->actingAs($user)->get('/danas')
        ->assertInertia(fn ($page) => $page->where('groups.1.key', 'deadlines')->has('groups.1.rows', 0));

    $this->actingAs($user)->get("/predmeti/{$matter->id}")
        ->assertInertia(fn ($page) => $page->where('recorded.0.status', 'fulfilled')
            ->where('recorded.0.sub', 'Ispunjeno podneskom I-2026/030 od 11.11.2026.'));
});

it('removes pending reminders of a fulfilled deadline', function () {
    [$user, $matter] = demoOffice();
    $deadline = Deadline::query()->firstOrFail();
    Reminder::query()->create([
        'office_id' => $matter->office_id, 'matter_id' => $matter->id, 'remind_on' => '2026-11-12',
        'text' => 'Sutra ističe rok', 'kind' => ReminderKind::DeadlineOneDay,
        'source_type' => $deadline->getMorphClass(), 'source_id' => $deadline->id,
    ]);

    $this->actingAs($user)->post('/posta/podnesak', submission($matter, ['fulfils' => [$deadline->id]]));

    expect($deadline->reminders()->count())->toBe(0);
});

it('records a submission without proof as such', function () {
    [$user, $matter] = demoOffice();

    $this->actingAs($user)->post('/posta/podnesak', submission($matter, ['proof' => null, 'delivery_method' => 'registered_mail']));

    $entry = MailEntry::query()->latest('id')->firstOrFail();
    expect($entry->proof_document_id)->toBeNull()
        ->and($entry->note)->toBe('preporučeno poštom · bez dokaza o predaji');

    $this->actingAs($user)->get('/posta?smjer=izlazna')
        ->assertInertia(fn ($page) => $page->where('entries.0.link', 'Dokaz o predaji nije priložen')
            ->where('entries.0.linkTone', 'proposed'));
});

it('reverts an approved cost sheet to draft when an item is suggested', function () {
    [$user, $matter] = demoOffice();
    $sheet = CostSheet::query()->firstOrFail();
    CostItem::query()->update(['status' => 'confirmed', 'confirmed_by' => $user->id, 'confirmed_at' => now(), 'amount' => 100]);
    $sheet->update(['status' => 'approved', 'approved_by' => $user->id, 'approved_at' => now()]);

    $this->actingAs($user)->post('/posta/podnesak', submission($matter));

    expect($sheet->fresh()->status->value)->toBe('draft');
});

it('cannot file into another office\'s matter', function () {
    [, $matter] = demoOffice();

    $this->actingAs(User::factory()->create())
        ->post('/posta/podnesak', submission($matter))
        ->assertNotFound();

    expect(MailEntry::query()->withoutGlobalScopes()->where('direction', 'out')->count())->toBe(1);
});
