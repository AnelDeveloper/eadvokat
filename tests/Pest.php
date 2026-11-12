<?php

use App\Demo\DemoScenario;
use App\Models\Matter;
use App\Models\Office;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->extend(TestCase::class)->in('Unit');

/**
 * Seeds the prototype's fictional office and matter and freezes the clock at
 * the demo "today" (11.11.2026. 08:05, Europe/Sarajevo).
 *
 * @return array{0: User, 1: Matter}
 */
function demoOffice(): array
{
    test()->travelTo(CarbonImmutable::parse('2026-11-11 08:05', 'Europe/Sarajevo'));

    $office = Office::factory()->create(['is_demo' => true]);
    $user = User::factory()->create([
        'office_id' => $office->id,
        'title' => 'adv.',
        'name' => 'Lejla Karić',
    ]);
    $matter = (new DemoScenario)->seed($office, $user);

    return [$user, $matter];
}
