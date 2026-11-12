<?php

namespace App\Contracts;

use App\Services\Speech\Transcription;

/**
 * Turns a dictated hearing note into a transcript plus independent proposals.
 * Only the fake driver exists; see DocumentExtractor for why.
 */
interface SpeechTranscriber
{
    public function transcribe(string $disk, string $path, string $mimeType): Transcription;
}
