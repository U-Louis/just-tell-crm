<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;

class CreateUserRequest extends CrmFormRequest
{
    protected function featureId(): ?string
    {
        return 'F7';
    }

    protected function prepareForValidation(): void
    {
        if ($this->exists('name')) {
            $this->merge([
                'name' => trim((string) $this->input('name')),
            ]);
        }
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $this->refuse('user.name.required', 'F8', [
            'name' => 'user.name.required',
        ]);
    }
}
