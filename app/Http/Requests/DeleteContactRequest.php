<?php

namespace App\Http\Requests;

class DeleteContactRequest extends CrmFormRequest
{
    protected function featureId(): ?string
    {
        return 'F15';
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [];
    }
}
