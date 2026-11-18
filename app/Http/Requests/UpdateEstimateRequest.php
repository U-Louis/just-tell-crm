<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;

class UpdateEstimateRequest extends CrmFormRequest
{
    protected function featureId(): ?string
    {
        return 'F22';
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
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $lines = $this->input('lines', []);
            if (! is_array($lines)) {
                return;
            }

            foreach ($lines as $line) {
                if (is_array($line) && (int) ($line['quantity'] ?? 0) >= 1) {
                    return;
                }
            }

            $this->refuse('estimate.lines.required', 'F23', [
                'lines' => 'estimate.lines.required',
            ]);
        });
    }
}
