<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;

class UpdateProductRequest extends CrmFormRequest
{
    protected function featureId(): ?string
    {
        return 'F20';
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
            'unit_price' => ['required', 'integer', 'min:0'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $fields = [];
        if ($validator->errors()->has('name')) {
            $fields['name'] = 'product.name.required';
        }
        if ($validator->errors()->has('unit_price')) {
            $fields['unit_price'] = 'product.unit_price.invalid';
        }

        if (isset($fields['name'])) {
            $this->refuse('product.name.required', 'F17', $fields);
        }

        $this->refuse('product.unit_price.invalid', 'F19', $fields);
    }
}
