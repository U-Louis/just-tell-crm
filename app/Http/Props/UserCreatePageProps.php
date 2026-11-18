<?php

namespace App\Http\Props;

final class UserCreatePageProps
{
    /**
     * @return array<string, mixed>
     */
    public static function make(): array
    {
        return [
            'urls' => [
                'store' => route('users.store'),
            ],
        ];
    }
}
