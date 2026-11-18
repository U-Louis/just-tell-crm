<?php

namespace App\Http\Requests;

class DeleteEstimateRequest extends CrmFormRequest
{
    protected function featureId(): ?string
    {
        return 'F24';
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [];
    }
}
