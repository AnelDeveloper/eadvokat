<?php

use App\Enums\DeadlineStatus;
use App\Enums\ItemStatus;
use App\Models\AuditLog;
use App\Models\CostItem;
use App\Models\Deadline;
use App\Models\Document;
use App\Models\Event;
use App\Models\Note;
use App\Models\Reminder;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(fn () => Storage::fake('local'));

function todaysHearing(): Event
{
    return Event::query()->where('title', 'Nastavak glavne rasprave')->firstOrFail();
}

function dictate(User $user): Document
{
    test()->actingAs($user)->post('/diktat', [
        'event_id' => todaysHearing()->id,
        'audio' => UploadedFile::fake()->create('diktat.webm', 40, 'audio/webm'),
    ])->assertRedirect();

    return Document::query()->where('kind', 'recording')->latest('id')->firstOrFail();
}

function hearingPayload(array $event = [], array $deadline = [], array $note = []): array
{
    return [
        'event' => ['date' => '2026-12-14', 'time' => '10:00', 'location' => '12', 'confirmed' => true, ...$event],
        'deadline' => ['due_date' => '2026-11-19', 'rule_ack' => true, 'start_ack' => true, 'due_ack' => true, 'confirmed' => true, ...$deadline],
        'note' => ['body' => 'Ročište odgođeno.', 'confirmed' => true, ...$note],
    ];
}

it('offers today\'s hearing for dictation', function () {
    [$user] = demoOffice();

    $this->actingAs($user)->get('/diktat')
        ->assertInertia(fn ($page) => $page->component('flow/Dictation')
            ->where('hearing.label', '65 0 P 084512 25 P · ročište 11.11.2026. u 11:00'));
});

it('stores the recording privately and shows three proposals', function () {
    [$user] = demoOffice();
    $recording = dictate($user);

    Storage::disk('local')->assertExists($recording->path);

    $this->actingAs($user)->get("/diktat/{$recording->id}")
        ->assertInertia(fn ($page) => $page->component('flow/DictationResult')
            ->where('proposal.event.heard', '21')
            ->where('proposal.event.options', ['12', '21'])
            ->where('proposal.deadline.dueDate', '2026-11-19')
            ->where('proposal.deadline.startDate', '2026-11-11')
            ->where('saved', null));

    expect(Event::query()->count())->toBe(5)
        ->and(Note::query()->count())->toBe(0);
});

it('saves everything as proposals when nothing is confirmed', function () {
    [$user] = demoOffice();
    $recording = dictate($user);

    $this->actingAs($user)->post("/diktat/{$recording->id}/sacuvaj", hearingPayload(
        ['location' => null, 'confirmed' => false],
        ['rule_ack' => false, 'start_ack' => false, 'due_ack' => false, 'confirmed' => false],
        ['confirmed' => false],
    ))->assertRedirect('/danas');

    $event = Event::query()->latest('id')->firstOrFail();
    expect($event->status)->toBe(ItemStatus::Proposed)
        ->and($event->uncertain)->toBe(['location' => ['12', '21']])
        ->and(Deadline::query()->latest('id')->first()->status)->toBe(DeadlineStatus::Proposed)
        ->and(Note::query()->first()->status)->toBe(ItemStatus::Proposed)
        ->and(Reminder::query()->where('kind', '!=', 'manual')->count())->toBe(0)
        ->and(todaysHearing()->outcome)->toBe('Ročište odgođeno');

    expect(AuditLog::query()->where('action', 'voice.saved')->first()->description)
        ->toBe('Glasovna bilješka sačuvana: potvrđeno 0 od 3 prijedloga.');

    $this->actingAs($user)->get('/danas')
        ->assertInertia(fn ($page) => $page->where('groups.3.key', 'review')->has('groups.3.rows', 4));
});

it('confirms all three, records the room correction and suggests a cost item', function () {
    [$user] = demoOffice();
    $recording = dictate($user);

    $this->actingAs($user)->post("/diktat/{$recording->id}/sacuvaj", hearingPayload())->assertRedirect('/danas');

    $event = Event::query()->latest('id')->firstOrFail();
    $deadline = Deadline::query()->latest('id')->firstOrFail();

    expect($event->status)->toBe(ItemStatus::Confirmed)
        ->and($event->location)->toBe('Sudnica 12')
        ->and($event->starts_at->setTimezone('Europe/Sarajevo')->format('Y-m-d H:i'))->toBe('2026-12-14 10:00')
        ->and($deadline->status)->toBe(DeadlineStatus::Confirmed)
        ->and($deadline->source_document_id)->toBe($recording->id)
        ->and($deadline->reminders()->count())->toBe(2);

    $cost = CostItem::query()->latest('id')->firstOrFail();
    expect($cost->title)->toBe('Zastupanje na ročištu (odgođeno)')
        ->and($cost->status)->toBe(ItemStatus::Proposed)
        ->and($cost->amount)->toBeNull()
        ->and($cost->source_label)->toBe('glasovna bilješka');

    expect(AuditLog::query()->where('action', 'voice.room_resolved')->first()->description)
        ->toBe('Glasovni unos: sudnica ispravljena 21 → 12.');
});

it('will not confirm the event while the room is unresolved', function () {
    [$user] = demoOffice();
    $recording = dictate($user);

    $this->actingAs($user)
        ->post("/diktat/{$recording->id}/sacuvaj", hearingPayload(['location' => null]))
        ->assertSessionHasErrors(['event.location' => 'Prvo provjerite sudnicu: 12 ili 21?']);

    expect(Event::query()->count())->toBe(5);
});

it('will not confirm a dictated deadline with only two acknowledgements', function () {
    [$user] = demoOffice();
    $recording = dictate($user);

    $this->actingAs($user)
        ->post("/diktat/{$recording->id}/sacuvaj", hearingPayload([], ['start_ack' => false]))
        ->assertSessionHasErrors('deadline.start_ack');

    expect(Deadline::query()->count())->toBe(1)
        ->and(Note::query()->count())->toBe(0);
});

it('saves a dictation only once', function () {
    [$user] = demoOffice();
    $recording = dictate($user);
    $this->actingAs($user)->post("/diktat/{$recording->id}/sacuvaj", hearingPayload());

    $this->actingAs($user)->post("/diktat/{$recording->id}/sacuvaj", hearingPayload())
        ->assertSessionHasErrors('recording');

    expect(Note::query()->count())->toBe(1);
});

it('confirms a saved proposal later, one by one', function () {
    [$user] = demoOffice();
    $recording = dictate($user);
    $this->actingAs($user)->post("/diktat/{$recording->id}/sacuvaj", hearingPayload(
        ['location' => null, 'confirmed' => false],
        ['confirmed' => false, 'rule_ack' => false, 'start_ack' => false, 'due_ack' => false],
        ['confirmed' => false],
    ));
    $event = Event::query()->latest('id')->firstOrFail();
    $note = Note::query()->firstOrFail();

    $this->actingAs($user)->post("/dogadjaji/{$event->id}/potvrda", ['date' => '2026-12-14', 'time' => '10:00'])
        ->assertSessionHasErrors('location');

    $this->actingAs($user)->post("/dogadjaji/{$event->id}/potvrda", ['date' => '2026-12-14', 'time' => '10:00', 'location' => '12']);
    $this->actingAs($user)->post("/biljeske/{$note->id}/potvrda", ['body' => 'Ispravljen tekst.']);

    expect($event->fresh()->status)->toBe(ItemStatus::Confirmed)
        ->and($event->fresh()->uncertain)->toBeNull()
        ->and($note->fresh()->body)->toBe('Ispravljen tekst.')
        ->and($note->fresh()->confirmed_by)->toBe($user->id);
});

it('keeps recordings private to their office', function () {
    [$user] = demoOffice();
    $recording = dictate($user);
    $hearingId = todaysHearing()->id;
    $stranger = User::factory()->create();

    $this->actingAs($stranger)->get("/diktat/{$recording->id}")->assertNotFound();
    $this->actingAs($stranger)->post('/diktat', [
        'event_id' => $hearingId,
        'audio' => UploadedFile::fake()->create('d.webm', 10, 'audio/webm'),
    ])->assertNotFound();
});
