<?php

namespace App\Http\Requests;

class ImportFixtureRequest extends CrmFormRequest
{
    protected function featureId(): ?string
    {
        return null;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [];
    }
}
