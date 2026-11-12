<?php

namespace App\Http\Controllers;

use App\Screens\TodayScreen;
use Inertia\Inertia;
use Inertia\Response;

class TodayController extends Controller
{
    public function __invoke(TodayScreen $screen): Response
    {
        return Inertia::render('Today', $screen->data($this->user()));
    }
}
