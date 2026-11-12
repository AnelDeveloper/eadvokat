<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProposeDeadlineRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'rule_text' => ['required', 'string', 'max:1000'],
            'days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'due_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'source_quote' => ['nullable', 'string', 'max:2000'],
            'mail_entry_id' => ['nullable', 'integer'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'naziv roka',
            'rule_text' => 'pravilo',
            'days' => 'broj dana',
            'start_date' => 'početak',
            'due_date' => 'posljednji dan roka',
            'source_quote' => 'navod iz dokumenta',
        ];
    }

    /**
     * @return array{title: string, rule_text: string, days: int|null, start_date: string, due_date: string, source_quote: string|null, mail_entry_id: int|null}
     */
    public function payload(): array
    {
        $quote = trim((string) $this->input('source_quote'));

        return [
            'title' => trim((string) $this->input('title')),
            'rule_text' => trim((string) $this->input('rule_text')),
            'days' => $this->filled('days') ? $this->integer('days') : null,
            'start_date' => (string) $this->input('start_date'),
            'due_date' => (string) $this->input('due_date'),
            'source_quote' => $quote === '' ? null : $quote,
            'mail_entry_id' => $this->filled('mail_entry_id') ? $this->integer('mail_entry_id') : null,
        ];
    }
}
