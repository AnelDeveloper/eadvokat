<?php

namespace App\Services\Extraction;

use App\Contracts\DocumentExtractor;
use App\Support\Clock;
use App\Support\CurrentOffice;

/**
 * Demo offices get the seed fixture (the fictional Rješenje of 06.11.2026.).
 * Real offices get no proposals at all: until a reading service is chosen,
 * the lawyer fills the fields from the uploaded document. Nothing is invented.
 * In both cases the receipt date is assumed to be today and flagged.
 */
final class FakeDocumentExtractor implements DocumentExtractor
{
    public function extract(string $disk, string $path, string $mimeType): Extraction
    {
        if (CurrentOffice::get()?->is_demo) {
            /** @var array<string, mixed> $data */
            $data = json_decode((string) file_get_contents(resource_path('fixtures/court-decision.json')), true, flags: JSON_THROW_ON_ERROR);
            $data['fields']['received_on'] = Clock::today()->toDateString();

            return Extraction::fromArray($data);
        }

        return new Extraction(
            name: __('mail.received_document'),
            paragraphs: [],
            fields: [
                'court' => '',
                'case_number' => '',
                'doc_type' => '',
                'document_date' => null,
                'received_on' => Clock::today()->toDateString(),
                'received_on_assumed' => true,
            ],
            quotes: [],
            deadline: null,
            fictional: false,
        );
    }
}
