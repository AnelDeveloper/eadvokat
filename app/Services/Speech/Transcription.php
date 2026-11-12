<?php

namespace App\Services\Speech;

/**
 * Transcript of a dictated hearing note plus three independent proposals.
 * Values the recording could not settle are listed in $event['location_options'].
 */
final readonly class Transcription
{
    /**
     * @param  array{title: string, date: string, time: string, location_heard: string, location_options: array<int, string>, venue: string|null, quote: string}  $event
     * @param  array{title: string, rule_text: string, days: int, basis: string, quote: string, highlight: string}  $deadline
     * @param  array{body: string}  $note
     * @param  array{title: string, outcome: string}  $hearingOutcome
     */
    public function __construct(
        public string $text,
        public array $event,
        public array $deadline,
        public array $note,
        public array $hearingOutcome,
        public bool $fictional,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'event' => $this->event,
            'deadline' => $this->deadline,
            'note' => $this->note,
            'hearing_outcome' => $this->hearingOutcome,
            'fictional' => $this->fictional,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            text: $data['text'],
            event: $data['event'],
            deadline: $data['deadline'],
            note: $data['note'],
            hearingOutcome: $data['hearing_outcome'],
            fictional: (bool) ($data['fictional'] ?? false),
        );
    }
}
