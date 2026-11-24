<?php

namespace App\Http\Requests;

use App\Domain\Crm\CompanySize;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;

class CreateContactRequest extends CrmFormRequest
{
    protected function featureId(): ?string
    {
        return 'F1';
    }

    protected function prepareForValidation(): void
    {
        if ($this->exists('name')) {
            $this->merge([
                'name' => trim((string) $this->input('name')),
            ]);
        }

        $this->blankToNull(['description', 'notes', 'company_size', 'owner_id']);
    }

    /**
     * @return array<string, list<string|In>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:255'],
            'company_size' => ['nullable', 'string', 'max:50', Rule::in(CompanySize::VALUES)],
            'owner_id' => ['nullable', 'integer', 'min:1'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        if ($validator->errors()->has('name')) {
            $this->refuse('contact.name.required', 'F2', [
                'name' => 'contact.name.required',
            ]);
        }

        $this->refuseProfile($validator, 'F1');
    }

    private function refuseProfile(Validator $validator, string $featureId): void
    {
        foreach ([
            'company_size' => 'contact.company_size.invalid',
            'owner_id' => 'contact.owner.invalid',
            'description' => 'contact.description.invalid',
            'notes' => 'contact.notes.invalid',
        ] as $field => $code) {
            if ($validator->errors()->has($field)) {
                $this->refuse($code, $featureId, [$field => $code]);
            }
        }

        parent::failedValidation($validator);
    }
}
