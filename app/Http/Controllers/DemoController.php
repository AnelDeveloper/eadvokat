<?php

namespace App\Http\Controllers;

use App\Demo\DemoProgress;
use App\Demo\DemoSessions;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DemoController extends Controller
{
    public function show(Request $request, DemoSessions $sessions, DemoProgress $progress): Response
    {
        /** @var User|null $signedIn */
        $signedIn = Auth::user();

        // Never replace a real user's session with a demo one.
        if ($signedIn !== null && ! $signedIn->office->is_demo) {
            return Inertia::render('Demo', ['blocked' => true, 'progress' => []]);
        }

        $sessions->ensure($request);

        return Inertia::render('Demo', ['blocked' => false, 'progress' => $progress->steps()]);
    }

    public function progress(DemoProgress $progress): JsonResponse
    {
        return response()->json(['progress' => $progress->steps()]);
    }

    public function reset(Request $request, DemoSessions $sessions): RedirectResponse
    {
        /** @var User|null $signedIn */
        $signedIn = Auth::user();

        if ($signedIn !== null && ! $signedIn->office->is_demo) {
            return to_route('demo');
        }

        $sessions->reset($request);

        return to_route('demo');
    }
}
