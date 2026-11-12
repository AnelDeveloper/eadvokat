<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveHearingNoteRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'event.date' => ['required', 'date_format:Y-m-d'],
            'event.time' => ['required', 'date_format:H:i'],
            'event.location' => ['nullable', 'string', 'max:20', 'required_if_accepted:event.confirmed'],
            'event.confirmed' => ['required', 'boolean'],
            'deadline.due_date' => ['required', 'date_format:Y-m-d'],
            // Rule 2 also applies to deadlines dictated at a hearing.
            'deadline.rule_ack' => ['boolean', 'accepted_if:deadline.confirmed,true'],
            'deadline.start_ack' => ['boolean', 'accepted_if:deadline.confirmed,true'],
            'deadline.due_ack' => ['boolean', 'accepted_if:deadline.confirmed,true'],
            'deadline.confirmed' => ['required', 'boolean'],
            'note.body' => ['required', 'string', 'max:5000'],
            'note.confirmed' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'event.location.required_if_accepted' => __('dictation.room_unresolved'),
            'deadline.rule_ack.accepted_if' => __('deadlines.ack_rule'),
            'deadline.start_ack.accepted_if' => __('deadlines.ack_start'),
            'deadline.due_ack.accepted_if' => __('deadlines.ack_due'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'event.date' => 'datum',
            'event.time' => 'vrijeme',
            'deadline.due_date' => 'posljednji dan roka',
            'note.body' => 'bilješka',
        ];
    }

    /**
     * @return array{
     *     event: array{date: string, time: string, location: string|null, confirmed: bool},
     *     deadline: array{due_date: string, rule_ack: bool, start_ack: bool, due_ack: bool, confirmed: bool},
     *     note: array{body: string, confirmed: bool}
     * }
     */
    public function payload(): array
    {
        $location = $this->input('event.location');

        return [
            'event' => [
                'date' => (string) $this->input('event.date'),
                'time' => (string) $this->input('event.time'),
                'location' => is_string($location) && $location !== '' ? $location : null,
                'confirmed' => $this->boolean('event.confirmed'),
            ],
            'deadline' => [
                'due_date' => (string) $this->input('deadline.due_date'),
                'rule_ack' => $this->boolean('deadline.rule_ack'),
                'start_ack' => $this->boolean('deadline.start_ack'),
                'due_ack' => $this->boolean('deadline.due_ack'),
                'confirmed' => $this->boolean('deadline.confirmed'),
            ],
            'note' => [
                'body' => (string) $this->input('note.body'),
                'confirmed' => $this->boolean('note.confirmed'),
            ],
        ];
    }
}
