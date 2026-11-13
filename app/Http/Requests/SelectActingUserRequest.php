<?php

namespace App\Http\Requests;

use App\Policies\CrmPolicy;
use Illuminate\Foundation\Http\FormRequest;

class SelectActingUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return app(CrmPolicy::class)->selectActingUser();
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
