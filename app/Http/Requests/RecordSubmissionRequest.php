<?php

namespace App\Http\Requests;

use App\Enums\DeliveryMethod;
use App\Models\Matter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

class RecordSubmissionRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'matter_id' => ['required', 'integer'],
            'doc_type' => ['required', 'string', 'max:255'],
            'sent_on' => ['required', 'date_format:Y-m-d'],
            'delivery_method' => ['required', Rule::enum(DeliveryMethod::class)],
            'proof' => ['nullable', 'file', 'mimetypes:image/jpeg,image/png,image/heic,image/heif,image/webp,application/pdf', 'max:20480'],
            'fulfils' => ['array'],
            'fulfils.*' => ['integer'],
            'reminder_text' => ['required', 'string', 'max:255'],
            'reminder_on' => ['required', 'date_format:Y-m-d', 'after_or_equal:sent_on'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'doc_type' => 'vrsta podneska',
            'sent_on' => 'datum predaje',
            'delivery_method' => 'način predaje',
            'proof' => 'dokaz o predaji',
            'reminder_text' => 'podsjetnik',
            'reminder_on' => 'dan podsjetnika',
        ];
    }

    public function matter(): Matter
    {
        return Matter::query()->findOrFail($this->integer('matter_id'));
    }

    /**
     * @return array{doc_type: string, sent_on: string, delivery_method: DeliveryMethod, proof: UploadedFile|null, fulfils: array<int, int>, reminder_text: string, reminder_on: string}
     */
    public function payload(): array
    {
        $proof = $this->file('proof');

        return [
            'doc_type' => (string) $this->input('doc_type'),
            'sent_on' => (string) $this->input('sent_on'),
            'delivery_method' => DeliveryMethod::from((string) $this->input('delivery_method')),
            'proof' => $proof instanceof UploadedFile ? $proof : null,
            'fulfils' => array_map('intval', (array) $this->input('fulfils', [])),
            'reminder_text' => (string) $this->input('reminder_text'),
            'reminder_on' => (string) $this->input('reminder_on'),
        ];
    }
}
