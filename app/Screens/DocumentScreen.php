<?php

namespace App\Screens;

use App\Models\Document;

/**
 * Izvorni dokument: a source document rendered as text, plus the original file.
 */
final class DocumentScreen
{
    /**
     * @return array<string, mixed>
     */
    public function data(Document $document): array
    {
        return [
            'id' => $document->id,
            'name' => $document->name,
            'paragraphs' => $document->paragraphs ?? collect(explode("\n", str_replace("\r", '', (string) $document->extracted_text)))
                ->filter()->map(fn ($text) => ['text' => $text])->values(),
            'isFictional' => $document->is_fictional,
            'fileUrl' => $document->hasFile() ? route('documents.file', $document) : null,
            'isImage' => str_starts_with((string) $document->mime_type, 'image/'),
        ];
    }
}
