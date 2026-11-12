<?php

namespace Database\Factories;

use App\Enums\Jurisdiction;
use App\Models\Matter;
use App\Models\Office;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Matter>
 */
class MatterFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'office_id' => Office::factory(),
            'case_number' => fake()->unique()->numerify('65 0 P ###### 26 P'),
            'title' => fake()->lastName().' protiv '.fake()->company(),
            'subject' => 'Radi isplate duga',
            'court' => 'Općinski sud u Sarajevu',
            'represented_side' => 'tužioca',
            'client_name' => fake()->name(),
            'jurisdiction' => Jurisdiction::Fbih,
        ];
    }
}
