<?php

namespace App\Http\Requests;

use App\Domain\Crm\ProductCategory;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

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

        $this->blankToNull(['description', 'category']);
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'unit_price' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100', Rule::in(ProductCategory::VALUES)],
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

        if ($validator->errors()->has('unit_price')) {
            $this->refuse('product.unit_price.invalid', 'F19', $fields);
        }

        if ($validator->errors()->has('category')) {
            $this->refuse('product.category.invalid', 'F20', [
                'category' => 'product.category.invalid',
            ]);
        }

        if ($validator->errors()->has('description')) {
            $this->refuse('product.description.invalid', 'F20', [
                'description' => 'product.description.invalid',
            ]);
        }

        parent::failedValidation($validator);
    }
}
