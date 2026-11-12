<?php

namespace App\Actions;

use App\Enums\CostSheetStatus;
use App\Enums\Jurisdiction;
use App\Models\CostSheet;
use App\Models\Matter;
use App\Models\User;
use App\Support\Audit;
use Illuminate\Support\Facades\DB;

final class CreateMatter
{
    /**
     * @param  array{case_number: string, title: string, subject: string, court: string, represented_side: string, client_name: string, jurisdiction: Jurisdiction}  $input
     */
    public function handle(User $user, array $input): Matter
    {
        return DB::transaction(function () use ($user, $input) {
            $matter = Matter::query()->create([...$input, 'office_id' => $user->office_id]);

            CostSheet::query()->create([
                'office_id' => $user->office_id,
                'matter_id' => $matter->id,
                'jurisdiction' => $matter->jurisdiction,
                'status' => CostSheetStatus::Draft,
            ]);

            Audit::record($matter, 'matter.created', __('audit.matter_created', ['case' => $matter->case_number]), $matter, [], [
                ...$input,
                'jurisdiction' => $matter->jurisdiction->value,
            ]);

            return $matter;
        });
    }
}
