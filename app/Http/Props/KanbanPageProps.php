<?php

namespace App\Http\Props;

use App\Domain\Crm\Contact;

final class KanbanPageProps
{
    /**
     * @param  list<Contact>  $contacts
     * @return array<string, mixed>
     */
    public static function make(array $contacts): array
    {
        $columns = [
            ['name' => 'new', 'contacts' => []],
            ['name' => 'qualified', 'contacts' => []],
            ['name' => 'estimate sent', 'contacts' => []],
        ];

        foreach ($contacts as $contact) {
            foreach ($columns as $index => $column) {
                if ($column['name'] !== $contact->column || $contact->id === null) {
                    continue;
                }

                $columns[$index]['contacts'][] = [
                    'id' => $contact->id,
                    'name' => $contact->name,
                    'showUrl' => route('contacts.show', $contact->id),
                ];
            }
        }

        return [
            'columns' => $columns,
            'urls' => [
                'store' => route('contacts.store'),
            ],
        ];
    }
}
