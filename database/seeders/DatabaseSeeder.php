<?php

namespace Database\Seeders;

use App\Demo\DemoScenario;
use App\Models\Office;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Local demo data: the prototype's fictional office, lawyer and matter.
     * Sign in as lejla.karic@rokovnik.test with password "demo-lozinka".
     */
    public function run(): void
    {
        $office = Office::query()->create([
            'name' => 'Advokatska kancelarija Karić (demo)',
            'is_demo' => true,
        ]);

        $lawyer = User::query()->create([
            'office_id' => $office->id,
            'title' => 'adv.',
            'name' => 'Lejla Karić',
            'email' => 'lejla.karic@rokovnik.test',
            'password' => 'demo-lozinka',
        ]);

        (new DemoScenario)->seed($office, $lawyer);
    }
}
