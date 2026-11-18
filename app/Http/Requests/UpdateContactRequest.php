<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;

class UpdateContactRequest extends CrmFormRequest
{
    protected function featureId(): ?string
    {
        return 'F13';
    }

    protected function prepareForValidation(): void
    {
        if ($this->exists('name')) {
            $this->merge([
                'name' => trim((string) $this->input('name')),
            ]);
        }

        $email = $this->input('email');
        $company = $this->input('company');

        $this->merge([
            'email' => is_string($email) ? trim($email) : $email,
            'company' => is_string($company) ? trim($company) : $company,
        ]);

        if ($this->input('email') === '') {
            $this->merge(['email' => null]);
        }

        if ($this->input('company') === '') {
            $this->merge(['company' => null]);
        }
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        if ($validator->errors()->has('name')) {
            $this->refuse('contact.name.required', 'F14', [
                'name' => 'contact.name.required',
            ]);
        }

        parent::failedValidation($validator);
    }
}
