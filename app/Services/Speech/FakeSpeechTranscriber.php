<?php

namespace App\Services\Speech;

use App\Contracts\SpeechTranscriber;

/**
 * Returns the prepared fictional hearing dictation for any recording.
 */
final class FakeSpeechTranscriber implements SpeechTranscriber
{
    public function transcribe(string $disk, string $path, string $mimeType): Transcription
    {
        /** @var array<string, mixed> $data */
        $data = json_decode((string) file_get_contents(resource_path('fixtures/hearing-note.json')), true, flags: JSON_THROW_ON_ERROR);

        return Transcription::fromArray($data);
    }
}
