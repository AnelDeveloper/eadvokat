<?php

namespace App\Actions;

use App\Contracts\SpeechTranscriber;
use App\Models\Document;
use App\Models\Event;
use App\Models\User;
use App\Support\Format;
use Illuminate\Http\UploadedFile;

/**
 * Stores a dictated hearing note on the private disk and keeps the
 * transcriber's proposals next to it. Nothing is created in the matter yet.
 */
final class RecordDictation
{
    public function __construct(private readonly SpeechTranscriber $transcriber) {}

    public function handle(User $user, Event $hearing, ?UploadedFile $audio): Document
    {
        $path = $audio?->store("recordings/{$user->office_id}", 'local');
        $mime = $audio ? (string) $audio->getMimeType() : 'audio/webm';

        $transcription = $this->transcriber->transcribe('local', (string) $path, $mime);

        return Document::query()->create([
            'office_id' => $user->office_id,
            'matter_id' => $hearing->matter_id,
            'kind' => 'recording',
            'name' => __('dictation.document_name', ['date' => Format::date($hearing->starts_at)]),
            'disk' => $path ? 'local' : null,
            'path' => $path ?: null,
            'original_name' => $audio?->getClientOriginalName(),
            'mime_type' => $audio ? $mime : null,
            'size' => $audio?->getSize(),
            'document_date' => $hearing->starts_at->toDateString(),
            'extracted_text' => $transcription->text,
            'paragraphs' => [['text' => $transcription->text]],
            'extraction' => [...$transcription->toArray(), 'event_id' => $hearing->id, 'saved' => null],
            'is_fictional' => $transcription->fictional,
            'uploaded_by' => $user->id,
        ]);
    }
}
