<?php

use App\Enums\CostSheetStatus;
use App\Enums\ItemStatus;
use App\Models\AuditLog;
use App\Models\CostItem;
use App\Models\CostSheet;
use App\Models\User;

function pendingItem(): CostItem
{
    return CostItem::query()->where('title', 'Odlazak u sud — uvid u spis')->firstOrFail();
}

it('shows the draft cost sheet as in the prototype', function () {
    [$user, $matter] = demoOffice();

    $this->actingAs($user)->get("/predmeti/{$matter->id}/troskovnik")
        ->assertInertia(fn ($page) => $page->component('matters/Costs')
            ->where('sheet.approved', false)
            ->where('sheet.pendingLabel', '1 stavka čeka pregled')
            ->where('sheet.total', '1.560,00')
            ->where('sheet.items.0.source', 'pošta I-2025/044')
            ->where('sheet.items.3.confirmed', false)
            ->where('isDemo', true));
});

it('confirms an item and records the amount in the audit log', function () {
    [$user] = demoOffice();
    $item = pendingItem();

    $this->actingAs($user)->post("/troskovi/stavke/{$item->id}/potvrda")->assertRedirect();

    expect($item->fresh()->status)->toBe(ItemStatus::Confirmed)
        ->and($item->fresh()->confirmed_by)->toBe($user->id)
        ->and(AuditLog::query()->latest('id')->first()->description)
        ->toBe('Stavka troškovnika „Odlazak u sud — uvid u spis“ potvrđena (iznos 120,00 KM).');
});

it('will not confirm an item without an amount', function () {
    [$user] = demoOffice();
    $item = pendingItem();
    $item->update(['amount' => null]);

    $this->actingAs($user)->post("/troskovi/stavke/{$item->id}/potvrda")->assertSessionHasErrors('amount');

    expect($item->fresh()->status)->toBe(ItemStatus::Proposed);
});

it('takes an edited item back to review', function () {
    [$user] = demoOffice();
    $item = CostItem::query()->where('status', 'confirmed')->firstOrFail();

    $this->actingAs($user)->patch("/troskovi/stavke/{$item->id}", ['amount' => '500.00']);

    $item->refresh();
    expect($item->amount)->toBe('500.00')
        ->and($item->status)->toBe(ItemStatus::Proposed)
        ->and(AuditLog::query()->latest('id')->first()->old_values['amount'])->toBe('480.00');
});

it('approves only when every item is confirmed and a tariff source is given', function () {
    [$user, $matter] = demoOffice();
    $sheet = CostSheet::query()->firstOrFail();

    $this->actingAs($user)->post("/predmeti/{$matter->id}/troskovnik/odobri")->assertSessionHasErrors('items');
    expect($sheet->fresh()->status)->toBe(CostSheetStatus::Draft);

    $this->actingAs($user)->post('/troskovi/stavke/'.pendingItem()->id.'/potvrda');
    $this->actingAs($user)->patch("/predmeti/{$matter->id}/troskovnik", ['jurisdiction' => 'fbih', 'tariff_source' => '']);
    $this->actingAs($user)->post("/predmeti/{$matter->id}/troskovnik/odobri")->assertSessionHasErrors('tariff_source');

    $this->actingAs($user)->patch("/predmeti/{$matter->id}/troskovnik", ['jurisdiction' => 'fbih', 'tariff_source' => 'Tarifa AK FBiH, Sl. novine FBiH (unosi advokat)']);
    $this->actingAs($user)->post("/predmeti/{$matter->id}/troskovnik/odobri")->assertSessionHasNoErrors();

    $sheet->refresh();
    expect($sheet->status)->toBe(CostSheetStatus::Approved)
        ->and($sheet->approved_by)->toBe($user->id)
        ->and(AuditLog::query()->where('action', 'cost_sheet.approved')->exists())->toBeTrue();
});

it('reverts an approved sheet to draft on any change', function (string $change) {
    [$user, $matter] = demoOffice();
    $item = pendingItem();
    $this->actingAs($user)->post("/troskovi/stavke/{$item->id}/potvrda");
    $this->actingAs($user)->post("/predmeti/{$matter->id}/troskovnik/odobri");
    expect(CostSheet::query()->first()->status)->toBe(CostSheetStatus::Approved);

    match ($change) {
        'amount' => $this->patch("/troskovi/stavke/{$item->id}", ['amount' => '130.00']),
        'item reverted' => $this->post("/troskovi/stavke/{$item->id}/vrati"),
        'item removed' => $this->delete("/troskovi/stavke/{$item->id}"),
        'jurisdiction' => $this->patch("/predmeti/{$matter->id}/troskovnik", ['jurisdiction' => 'rs', 'tariff_source' => 'Demo tarifa (izmišljena, nije zvanični izvor)']),
        'tariff source' => $this->patch("/predmeti/{$matter->id}/troskovnik", ['jurisdiction' => 'fbih', 'tariff_source' => 'Druga tarifa']),
    };

    expect(CostSheet::query()->first()->status)->toBe(CostSheetStatus::Draft)
        ->and(AuditLog::query()->where('action', 'cost_sheet.reverted')->exists())->toBeTrue();
})->with(['amount', 'item reverted', 'item removed', 'jurisdiction', 'tariff source']);

it('records removal with the old values', function () {
    [$user] = demoOffice();
    $item = pendingItem();

    $this->actingAs($user)->delete("/troskovi/stavke/{$item->id}");

    $log = AuditLog::query()->where('action', 'cost_item.removed')->firstOrFail();
    expect(CostItem::query()->find($item->id))->toBeNull()
        ->and($log->old_values['amount'])->toBe('120.00')
        ->and($log->description)->toBe('Stavka troškovnika „Odlazak u sud — uvid u spis“ uklonjena iz nacrta.');
});

it('exports the draft as a PDF for the lawyer only', function () {
    [$user, $matter] = demoOffice();

    $response = $this->actingAs($user)->get("/predmeti/{$matter->id}/troskovnik/pdf")->assertOk();

    expect($response->headers->get('Content-Type'))->toBe('application/pdf')
        ->and($response->headers->get('Content-Disposition'))->toContain('troskovnik-nacrt-65-0-p-084512-25-p.pdf')
        ->and(substr($response->getContent(), 0, 4))->toBe('%PDF');

    $this->actingAs(User::factory()->create())->get("/predmeti/{$matter->id}/troskovnik/pdf")->assertNotFound();
});

it('does not let another office touch cost items', function () {
    demoOffice();
    $item = pendingItem();

    $this->actingAs(User::factory()->create())->post("/troskovi/stavke/{$item->id}/potvrda")->assertNotFound();

    expect($item->fresh()->status)->toBe(ItemStatus::Proposed);
});
