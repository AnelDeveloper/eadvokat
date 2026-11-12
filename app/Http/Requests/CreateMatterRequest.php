<?php

namespace App\Http\Requests;

use App\Enums\Jurisdiction;
use App\Support\CurrentOffice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateMatterRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'case_number' => ['required', 'string', 'max:100', Rule::unique('matters')->where('office_id', CurrentOffice::id())],
            'title' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'court' => ['required', 'string', 'max:255'],
            'represented_side' => ['required', 'string', 'max:100'],
            'client_name' => ['required', 'string', 'max:255'],
            'jurisdiction' => ['required', Rule::enum(Jurisdiction::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'case_number' => 'broj predmeta',
            'title' => 'naziv predmeta',
            'subject' => 'vrsta spora',
            'court' => 'sud',
            'represented_side' => 'koga zastupamo',
            'client_name' => 'stranka',
            'jurisdiction' => 'nadležnost',
        ];
    }

    /**
     * @return array{case_number: string, title: string, subject: string, court: string, represented_side: string, client_name: string, jurisdiction: Jurisdiction}
     */
    public function payload(): array
    {
        return [
            'case_number' => trim((string) $this->input('case_number')),
            'title' => trim((string) $this->input('title')),
            'subject' => trim((string) $this->input('subject')),
            'court' => trim((string) $this->input('court')),
            'represented_side' => trim((string) $this->input('represented_side')),
            'client_name' => trim((string) $this->input('client_name')),
            'jurisdiction' => Jurisdiction::from((string) $this->input('jurisdiction')),
        ];
    }
}
