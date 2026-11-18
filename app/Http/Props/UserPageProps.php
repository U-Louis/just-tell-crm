<?php

namespace App\Http\Props;

use App\Domain\Crm\User;

final class UserPageProps
{
    /**
     * @return array<string, mixed>
     */
    public static function make(User $user): array
    {
        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
            ],
            'urls' => [
                'update' => route('users.update', $user->id),
                'destroy' => route('users.destroy', $user->id),
                'pdf' => route('users.pdf', $user->id),
            ],
        ];
    }
}
