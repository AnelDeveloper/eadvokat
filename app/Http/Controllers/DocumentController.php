<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Screens\DocumentScreen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    private const INLINE_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];

    public function show(Request $request, Document $document, DocumentScreen $screen): Response
    {
        Gate::authorize('view', $document);

        $back = $this->sameOriginPath(url()->previous(), $request)
            ?? ($document->matter_id ? route('matters.show', $document->matter_id, false) : route('today', [], false));

        return Inertia::render('flow/DocumentViewer', [
            'document' => $screen->data($document),
            'backUrl' => $back,
        ]);
    }

    /**
     * Uploaded files live on a private disk and are only served here (rule 10).
     */
    public function file(Document $document): StreamedResponse
    {
        Gate::authorize('view', $document);
        abort_unless($document->hasFile(), 404);

        // Only raster images and PDFs are shown inline, with a forced type;
        // anything else downloads. The sandbox CSP stops scripts in any case.
        $mime = (string) $document->mime_type;
        $inline = in_array($mime, self::INLINE_TYPES, true);

        return Storage::disk((string) $document->disk)->response(
            (string) $document->path,
            $document->original_name,
            [
                'Content-Type' => $inline ? $mime : 'application/octet-stream',
                'Cache-Control' => 'private, no-store',
                'X-Content-Type-Options' => 'nosniff',
                'Content-Security-Policy' => "sandbox; default-src 'none'; img-src 'self'; style-src 'unsafe-inline'",
            ],
            $inline ? 'inline' : 'attachment',
        );
    }

    /**
     * Back link: only a path on this exact origin, returned as a relative URL.
     */
    private function sameOriginPath(string $previous, Request $request): ?string
    {
        $parts = parse_url($previous);

        if ($parts === false || isset($parts['user']) || isset($parts['pass'])
            || ($parts['scheme'] ?? null) !== $request->getScheme()
            || ($parts['host'] ?? null) !== $request->getHost()
            || ($parts['port'] ?? ($request->getScheme() === 'https' ? 443 : 80)) !== $request->getPort()) {
            return null;
        }

        $path = $parts['path'] ?? '/';

        if (! str_starts_with($path, '/') || str_starts_with($path, '//')) {
            return null;
        }

        $relative = $path.(isset($parts['query']) ? '?'.$parts['query'] : '');

        return $relative === $request->getRequestUri() ? null : $relative;
    }
}
