<?php

namespace App\Http\Props;

use App\Domain\Crm\CompanySize;

final class ContactCreatePageProps
{
    /**
     * @return array<string, mixed>
     */
    public static function make(): array
    {
        return [
            'companySizes' => CompanySize::VALUES,
            'urls' => [
                'store' => route('contacts.store'),
            ],
        ];
    }
}
