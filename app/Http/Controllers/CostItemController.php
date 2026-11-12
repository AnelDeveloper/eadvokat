<?php

namespace App\Http\Controllers;

use App\Actions\Costs\ManageCostItems;
use App\Models\CostItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class CostItemController extends Controller
{
    public function update(Request $request, CostItem $item, ManageCostItems $action): RedirectResponse
    {
        Gate::authorize('update', $item);
        $validated = $request->validate([
            'amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
        ], attributes: ['amount' => 'iznos']);

        $amount = $validated['amount'] ?? null;
        $action->updateAmount($item, $amount === null ? null : number_format((float) $amount, 2, '.', ''));

        return back();
    }

    public function confirm(CostItem $item, ManageCostItems $action): RedirectResponse
    {
        Gate::authorize('update', $item);
        $action->confirm($this->user(), $item);
        Inertia::flash('toast', __('costs.item_confirmed'));

        return back();
    }

    public function revert(CostItem $item, ManageCostItems $action): RedirectResponse
    {
        Gate::authorize('update', $item);
        $action->revertToReview($item);
        Inertia::flash('toast', __('costs.item_reverted'));

        return back();
    }

    public function destroy(CostItem $item, ManageCostItems $action): RedirectResponse
    {
        Gate::authorize('delete', $item);
        $action->remove($item);
        Inertia::flash('toast', __('costs.item_removed'));

        return back();
    }
}
