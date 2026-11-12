<?php

namespace App\Http\Controllers;

use App\Models\Matter;
use App\Screens\MatterScreens;
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
