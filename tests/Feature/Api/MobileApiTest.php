<?php

use App\Enums\DeadlineStatus;
use App\Models\Deadline;
use App\Models\Document;
use App\Models\Event;
use App\Models\Matter;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

beforeEach(fn () => Storage::fake('local'));

it('issues a token for valid credentials only', function () {
    $user = User::factory()->create(['email' => 'lejla@example.test']);

    $this->postJson('/api/v1/auth/token', ['email' => 'lejla@example.test', 'password' => 'pogresno', 'device_name' => 'iPhone'])
        ->assertStatus(422)
        ->assertJsonPath('errors.email.0', 'Pogrešan e-mail ili lozinka.');

    $token = $this->postJson('/api/v1/auth/token', ['email' => 'lejla@example.test', 'password' => 'password', 'device_name' => 'iPhone'])
        ->assertOk()
        ->json('token');

    $this->withToken($token)->getJson('/api/v1/me')->assertOk()->assertJsonPath('user.id', $user->id);
});

it('revokes the token on sign-out', function () {
    $user = User::factory()->create();
    $token = $user->createToken('iPhone')->plainTextToken;

    $this->withToken($token)->deleteJson('/api/v1/auth/token')->assertOk();

    expect($user->tokens()->count())->toBe(0);
});

it('rejects requests without a token', function () {
    $this->getJson('/api/v1/today')->assertUnauthorized();
});

it('serves the same Danas briefing as the web app', function () {
    [$user] = demoOffice();
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/today')
        ->assertOk()
        ->assertJsonPath('date', 'srijeda, 11. novembar 2026.')
        ->assertJsonPath('groups.1.rows.0.status', 'urgent')
        ->assertJsonPath('groups.3.rows.0.href', '/predmeti/'.Matter::query()->first()->id.'/troskovnik');
});

it('keeps offices apart over the API', function () {
    [, $matter] = demoOffice();
    Sanctum::actingAs(User::factory()->create());

    $this->getJson("/api/v1/matters/{$matter->id}")->assertNotFound();
    $this->getJson('/api/v1/matters')->assertJsonCount(0, 'matters');
});

it('runs capture → review → proposal → confirmation over the API', function () {
    [$user, $matter] = demoOffice();
    Sanctum::actingAs($user);

    $next = $this->post('/api/v1/mail/capture', ['file' => UploadedFile::fake()->image('r.jpg')], ['Accept' => 'application/json'])
        ->assertOk()->json('next');
    $documentId = (int) str($next)->afterLast('/')->toString();

    $this->getJson("/api/v1/mail/review/{$documentId}")->assertOk()->assertJsonPath('registerNumber', 'U-2026/041');

    $next = $this->postJson("/api/v1/mail/review/{$documentId}", [
        'court' => 'Općinski sud u Sarajevu',
        'case_number' => '65 0 P 084512 25 P',
        'doc_type' => 'Rješenje — dostava nalaza vještaka',
        'document_date' => '2026-11-06',
        'received_on' => '2026-11-11',
    ])->assertOk()->assertJsonPath('message', 'Unos U-2026/041 je sačuvan u knjigu pošte.')->json('next');

    $deadline = Deadline::query()->latest('id')->firstOrFail();
    expect($next)->toBe("/rokovi/{$deadline->id}/prijedlog");

    $payload = [
        'jurisdiction' => 'fbih', 'start_date' => '2026-11-11', 'due_date' => '2026-11-26',
        'rule_ack' => true, 'start_ack' => true, 'due_ack' => false,
        'remind_3_days' => true, 'remind_1_day' => true,
    ];

    $this->postJson("/api/v1/deadlines/{$deadline->id}/confirm", $payload)->assertStatus(422)->assertJsonValidationErrors('due_ack');
    expect($deadline->fresh()->status)->toBe(DeadlineStatus::Proposed);

    $this->postJson("/api/v1/deadlines/{$deadline->id}/confirm", [...$payload, 'due_ack' => true])
        ->assertOk()
        ->assertJsonPath('next', "/predmeti/{$matter->id}");

    expect($deadline->fresh()->status)->toBe(DeadlineStatus::Confirmed)
        ->and($deadline->reminders()->count())->toBe(2);
});

it('records a dictated hearing note over the API', function () {
    [$user] = demoOffice();
    Sanctum::actingAs($user);
    $hearing = Event::query()->where('title', 'Nastavak glavne rasprave')->firstOrFail();

    $next = $this->post('/api/v1/dictation', [
        'event_id' => $hearing->id,
        'audio' => UploadedFile::fake()->create('d.m4a', 20, 'audio/mp4'),
    ], ['Accept' => 'application/json'])->assertOk()->json('next');

    $recording = Document::query()->where('kind', 'recording')->firstOrFail();
    expect($next)->toBe("/diktat/{$recording->id}");

    $this->getJson("/api/v1/dictation/{$recording->id}")->assertJsonPath('proposal.event.heard', '21');

    $this->postJson("/api/v1/dictation/{$recording->id}/save", [
        'event' => ['date' => '2026-12-14', 'time' => '10:00', 'location' => null, 'confirmed' => true],
        'deadline' => ['due_date' => '2026-11-19', 'rule_ack' => false, 'start_ack' => false, 'due_ack' => false, 'confirmed' => false],
        'note' => ['body' => 'Bilješka.', 'confirmed' => false],
    ])->assertStatus(422)->assertJsonValidationErrors('event.location');
});

it('serves files and the PDF only with a valid token', function () {
    [$user, $matter] = demoOffice();
    Sanctum::actingAs($user);

    $this->get("/api/v1/matters/{$matter->id}/costs/pdf")->assertOk()->assertHeader('Content-Type', 'application/pdf');

    Sanctum::actingAs(User::factory()->create());
    $this->get("/api/v1/matters/{$matter->id}/costs/pdf", ['Accept' => 'application/json'])->assertNotFound();
});
