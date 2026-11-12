<?php

namespace App\Actions;

use App\Contracts\DocumentExtractor;
use App\Models\Document;
use App\Models\User;
use Illuminate\Http\UploadedFile;

/**
 * Stores a photographed or uploaded court document on the private disk and
 * keeps the extractor's proposal next to it. Nothing is registered yet.
 */
final class CaptureDocument
{
    public function __construct(private readonly DocumentExtractor $extractor) {}

    public function handle(User $user, UploadedFile $file, string $kind = 'court_document'): Document
    {
        $path = $file->store("documents/{$user->office_id}", 'local');
        $mime = (string) $file->getMimeType();

        $extraction = $kind === 'court_document'
            ? $this->extractor->extract('local', (string) $path, $mime)
            : null;

        return Document::query()->create([
            'office_id' => $user->office_id,
            'kind' => $kind,
            'name' => $extraction->name ?? $file->getClientOriginalName(),
            'disk' => 'local',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $mime,
            'size' => $file->getSize(),
            'document_date' => $extraction?->fields['document_date'] ?? null,
            'paragraphs' => $extraction?->paragraphs,
            'extracted_text' => $extraction ? collect($extraction->paragraphs)->pluck('text')->implode("\n") : null,
            'extraction' => $extraction?->toArray(),
            'is_fictional' => $extraction->fictional ?? false,
            'uploaded_by' => $user->id,
        ]);
    }
}
