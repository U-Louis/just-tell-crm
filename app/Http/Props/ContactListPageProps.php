<?php

namespace App\Http\Props;

use App\Domain\Crm\Contact;

final class ContactListPageProps
{
    /**
     * @param  list<Contact>  $contacts
     * @return array<string, mixed>
     */
    public static function make(array $contacts): array
    {
        return [
            'contacts' => array_map(function (Contact $contact) {
                return [
                    'id' => $contact->id,
                    'name' => $contact->name,
                    'column' => $contact->column,
                    'showUrl' => route('contacts.show', $contact->id),
                ];
            }, $contacts),
        ];
    }
}
