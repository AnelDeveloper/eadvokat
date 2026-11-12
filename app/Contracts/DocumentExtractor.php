<?php

namespace App\Contracts;

use App\Services\Extraction\Extraction;

/**
 * Reads a captured court document and proposes register fields, quoted
 * passages and a possible deadline. Never interprets the law; it quotes.
 *
 * Only the fake driver exists: no external AI or OCR service may be called
 * until the office agrees on storage and a no-training provider contract.
 */
interface DocumentExtractor
{
    public function extract(string $disk, string $path, string $mimeType): Extraction;
}
