<?php

namespace App\Http\Requests;

use App\Concerns\PasswordValidationRules;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterOfficeRequest extends FormRequest
{
    use PasswordValidationRules;

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => mb_strtolower(trim($this->input('email')))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'office_name' => ['required', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'password' => $this->passwordRules(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'office_name' => 'naziv kancelarije',
            'title' => 'titula',
            'name' => 'ime i prezime',
            'email' => 'e-mail',
            'password' => 'lozinka',
        ];
    }

    /**
     * @return array{office_name: string, title: string|null, name: string, email: string, password: string}
     */
    public function payload(): array
    {
        return [
            'office_name' => (string) $this->input('office_name'),
            'title' => $this->input('title') !== null ? (string) $this->input('title') : null,
            'name' => (string) $this->input('name'),
            'email' => (string) $this->input('email'),
            'password' => (string) $this->input('password'),
        ];
    }
}
