<?php

namespace App\Http\Controllers;

use App\Demo\DemoProgress;
use App\Screens\CalendarScreen;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CalendarController extends Controller
{
    public function __invoke(Request $request, CalendarScreen $screen, DemoProgress $demo): Response
    {
        $demo->markSeen($this->office(), 'calendar');

        return Inertia::render('Calendar', $screen->data(
            $request->query('mjesec') !== null ? (string) $request->query('mjesec') : null,
            $request->query('dan') !== null ? (int) $request->query('dan') : null,
        ));
    }
}
