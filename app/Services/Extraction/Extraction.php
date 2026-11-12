<?php

namespace App\Services\Extraction;

/**
 * Result of reading a document. All values are proposals for the lawyer.
 */
final readonly class Extraction
{
    /**
     * @param  array<int, array{text: string, align?: string, bold?: bool, highlighted?: bool}>  $paragraphs
     * @param  array{court: string, case_number: string, doc_type: string, document_date: string|null, received_on: string|null, received_on_assumed: bool}  $fields
     * @param  array<int, array{text: string, note: string}>  $quotes
     * @param  array{title: string, rule_text: string, days: int, basis: string, quote: string, highlight: string}|null  $deadline
     */
    public function __construct(
        public string $name,
        public array $paragraphs,
        public array $fields,
        public array $quotes,
        public ?array $deadline,
        public bool $fictional,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'paragraphs' => $this->paragraphs,
            'fields' => $this->fields,
            'quotes' => $this->quotes,
            'deadline' => $this->deadline,
            'fictional' => $this->fictional,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            paragraphs: $data['paragraphs'],
            fields: $data['fields'],
            quotes: $data['quotes'],
            deadline: $data['deadline'] ?? null,
            fictional: (bool) ($data['fictional'] ?? false),
        );
    }
}
