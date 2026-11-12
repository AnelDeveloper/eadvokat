<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function show(Request $request, Document $document): Response
    {
        Gate::authorize('view', $document);

        $previous = url()->previous();
        $back = str_starts_with($previous, url('/')) && $previous !== $request->fullUrl()
            ? $previous
            : ($document->matter_id ? route('matters.show', $document->matter_id) : route('today'));

        return Inertia::render('flow/DocumentViewer', [
            'document' => [
                'id' => $document->id,
                'name' => $document->name,
                'paragraphs' => $document->paragraphs ?? collect(explode("\n", str_replace("\r", '', (string) $document->extracted_text)))
                    ->filter()->map(fn ($text) => ['text' => $text])->values(),
                'isFictional' => $document->is_fictional,
                'fileUrl' => $document->hasFile() ? route('documents.file', $document) : null,
                'isImage' => str_starts_with((string) $document->mime_type, 'image/'),
            ],
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

        return Storage::disk((string) $document->disk)->response(
            (string) $document->path,
            $document->original_name,
            ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff'],
        );
    }
}
