<?php

namespace App\Http\Requests;

class DeleteProductRequest extends CrmFormRequest
{
    protected function featureId(): ?string
    {
        return 'F37';
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [];
    }
}
