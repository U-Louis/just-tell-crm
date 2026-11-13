<?php

namespace App\Http\Errors;

final class ErrorMessages
{
    /**
     * @var array<string, string>
     */
    public const MAP = [
        'contact.name.required' => 'A contact name is required.',
        'contact.email.required' => 'A contact email is required.',
        'user.required' => 'Choose an acting user.',
    ];
}
