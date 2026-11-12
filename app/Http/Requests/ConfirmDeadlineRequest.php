<?php

namespace App\Http\Requests;

use App\Enums\Jurisdiction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Rule 2 at the HTTP boundary: all three acknowledgements must be given.
 */
class ConfirmDeadlineRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'jurisdiction' => ['required', Rule::enum(Jurisdiction::class)],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'due_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'rule_ack' => ['accepted'],
            'start_ack' => ['accepted'],
            'due_ack' => ['accepted'],
            'remind_3_days' => ['required', 'boolean'],
            'remind_1_day' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'rule_ack.accepted' => __('deadlines.ack_rule'),
            'start_ack.accepted' => __('deadlines.ack_start'),
            'due_ack.accepted' => __('deadlines.ack_due'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'jurisdiction' => 'nadležnost',
            'start_date' => 'početak',
            'due_date' => 'posljednji dan roka',
        ];
    }

    /**
     * @return array{jurisdiction: Jurisdiction, start_date: string, due_date: string, rule_ack: bool, start_ack: bool, due_ack: bool, remind_3_days: bool, remind_1_day: bool}
     */
    public function payload(): array
    {
        return [
            'jurisdiction' => Jurisdiction::from((string) $this->input('jurisdiction')),
            'start_date' => (string) $this->input('start_date'),
            'due_date' => (string) $this->input('due_date'),
            'rule_ack' => $this->boolean('rule_ack'),
            'start_ack' => $this->boolean('start_ack'),
            'due_ack' => $this->boolean('due_ack'),
            'remind_3_days' => $this->boolean('remind_3_days'),
            'remind_1_day' => $this->boolean('remind_1_day'),
        ];
    }
}
