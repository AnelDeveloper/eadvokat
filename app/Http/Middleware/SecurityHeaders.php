<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Browser hardening for every web and API response. A response that already
 * sets its own Content-Security-Policy (served documents) keeps it.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);
        $headers = $response->headers;

        $headers->set('X-Content-Type-Options', 'nosniff');
        $headers->set('X-Frame-Options', 'SAMEORIGIN');
        $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        // Camera and microphone are used by capture and dictation on this origin only.
        $headers->set('Permissions-Policy', 'camera=(self), microphone=(self), geolocation=(), payment=(), usb=()');

        if (! $headers->has('Content-Security-Policy')) {
            $headers->set('Content-Security-Policy', "frame-ancestors 'self'; base-uri 'self'; object-src 'none'; form-action 'self'");
        }

        if ($request->isSecure()) {
            $headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Signed-in pages hold client data: never keep them in a shared or back-button cache.
        if ($request->user() !== null) {
            $headers->set('Cache-Control', 'private, no-store');
        }

        return $response;
    }
}
