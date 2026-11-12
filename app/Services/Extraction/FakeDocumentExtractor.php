<?php

namespace App\Services\Extraction;

use App\Contracts\DocumentExtractor;
use App\Support\Clock;

/**
 * Returns the seed fixture (the fictional Rješenje of 06.11.2026.) for any
 * file. The receipt date is not in the document, so it is assumed to be today
 * and flagged for the lawyer to check.
 */
final class FakeDocumentExtractor implements DocumentExtractor
{
    public function extract(string $disk, string $path, string $mimeType): Extraction
    {
        /** @var array<string, mixed> $data */
        $data = json_decode((string) file_get_contents(resource_path('fixtures/court-decision.json')), true, flags: JSON_THROW_ON_ERROR);

        $data['fields']['received_on'] = Clock::today()->toDateString();

        return Extraction::fromArray($data);
    }
}
