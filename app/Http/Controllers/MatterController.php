<?php

namespace App\Http\Controllers;

use App\Actions\CreateMatter;
use App\Enums\Jurisdiction;
use App\Http\Requests\CreateMatterRequest;
use App\Models\Matter;
use App\Screens\MatterScreens;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class MatterController extends Controller
{
    public function __construct(private readonly MatterScreens $screens) {}

    public function index(): Response
    {
        return Inertia::render('matters/Index', $this->screens->index((bool) $this->office()->is_demo));
    }

    public function create(): Response
    {
        return Inertia::render('matters/Create', [
            'jurisdictions' => collect(Jurisdiction::cases())->map(fn (Jurisdiction $j) => ['value' => $j->value, 'label' => $j->shortLabel()]),
        ]);
    }

    public function store(CreateMatterRequest $request, CreateMatter $action): RedirectResponse
    {
        $matter = $action->handle($this->user(), $request->payload());
        Inertia::flash('toast', __('messages.matter_created', ['case' => $matter->case_number]));

        return to_route('matters.show', $matter);
    }

    public function show(Matter $matter): Response
    {
        Gate::authorize('view', $matter);

        return Inertia::render('matters/Timeline', $this->screens->timeline($matter));
    }

    public function audit(Matter $matter): Response
    {
        Gate::authorize('view', $matter);

        return Inertia::render('matters/Audit', $this->screens->audit($matter));
    }
}
