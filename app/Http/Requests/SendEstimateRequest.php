<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;

class SendEstimateRequest extends CrmFormRequest
{
    protected function featureId(): ?string
    {
        return 'F4';
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'lines.*.quantity' => ['required', 'integer', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $lines = $this->input('lines', []);
            if (! is_array($lines)) {
                return;
            }

            $positive = false;
            foreach ($lines as $line) {
                if (is_array($line) && (int) ($line['quantity'] ?? 0) >= 1) {
                    $positive = true;
                }
            }

            if (! $positive) {
                $validator->errors()->add('lines', 'Add a product quantity.');
            }
        });
    }
}
