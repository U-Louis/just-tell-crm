<?php

namespace App\Http\Props;

final class ContactCreatePageProps
{
    /**
     * @return array<string, mixed>
     */
    public static function make(): array
    {
        return [
            'urls' => [
                'store' => route('contacts.store'),
            ],
        ];
    }
}
