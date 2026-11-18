<?php

namespace App\Http\Requests;

class DeleteUserRequest extends CrmFormRequest
{
    protected function featureId(): ?string
    {
        return 'F12';
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [];
    }
}
