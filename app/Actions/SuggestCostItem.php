<?php

namespace App\Actions;

use App\Actions\Costs\RevertSheetToDraft;
use App\Enums\CostSheetStatus;
use App\Enums\ItemStatus;
use App\Models\CostItem;
use App\Models\CostSheet;
use App\Models\Matter;
use App\Support\Audit;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * Rule 4: a recorded action becomes a *proposed* cost item without an amount.
 * The lawyer enters or confirms the amount; no tariff data is bundled.
 */
final class SuggestCostItem
{
    public function __construct(private readonly RevertSheetToDraft $revert) {}

    public function handle(Matter $matter, string $title, CarbonInterface $date, Model $source, string $sourceLabel, ?int $documentId = null, ?string $quote = null): CostItem
    {
        $sheet = CostSheet::query()->firstOrCreate(['matter_id' => $matter->id], [
            'office_id' => $matter->office_id,
            'jurisdiction' => $matter->jurisdiction,
            'status' => CostSheetStatus::Draft,
        ]);

        $item = CostItem::query()->create([
            'office_id' => $matter->office_id,
            'cost_sheet_id' => $sheet->id,
            'title' => $title,
            'action_date' => $date->toDateString(),
            'source_type' => $source->getMorphClass(),
            'source_id' => $source->getKey(),
            'source_label' => $sourceLabel,
            'source_document_id' => $documentId,
            'source_quote' => $quote,
            'amount' => null,
            'status' => ItemStatus::Proposed,
        ]);

        Audit::record($matter, 'cost_item.suggested', __('audit.cost_suggested', ['title' => $title]), $item, [], [
            'title' => $title,
            'action_date' => $date->toDateString(),
            'source' => $sourceLabel,
        ]);

        $this->revert->handle($sheet);

        return $item;
    }
}
