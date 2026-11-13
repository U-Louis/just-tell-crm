<?php

namespace App\Http\Requests;

class QualifyContactRequest extends CrmFormRequest
{
    protected function featureId(): ?string
    {
        return 'F3';
    }

    protected function prepareForValidation(): void
    {
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
            'email' => ['nullable', 'email', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
        ];
    }
}
