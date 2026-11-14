<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * After the trial ends the office keeps all its data, but the app itself is
 * closed: the web shows the "trial ended" page, the API answers 403.
 */
class EnsureTrialActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $office = $request->user()?->office;

        if ($office !== null && $office->trialExpired()) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => __('trial.expired'), 'code' => 'trial_expired'], 403);
            }

            return redirect()->route('trial.expired');
        }

        return $next($request);
    }
}
