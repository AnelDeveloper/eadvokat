<?php

namespace App\Http\Requests;

use App\Models\Matter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SaveIncomingMailRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'court' => ['required', 'string', 'max:255'],
            'case_number' => ['required', 'string', 'max:255'],
            'doc_type' => ['required', 'string', 'max:255'],
            'document_date' => ['nullable', 'date_format:Y-m-d'],
            'received_on' => ['required', 'date_format:Y-m-d'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if (! $validator->errors()->has('case_number') && $this->matter() === null) {
                $validator->errors()->add('case_number', __('mail.unknown_matter'));
            }
        }];
    }

    public function matter(): ?Matter
    {
        return Matter::query()->where('case_number', trim((string) $this->input('case_number')))->first();
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'court' => 'sud',
            'case_number' => 'broj predmeta',
            'doc_type' => 'vrsta dokumenta',
            'document_date' => 'datum dokumenta',
            'received_on' => 'datum prijema',
        ];
    }
}
