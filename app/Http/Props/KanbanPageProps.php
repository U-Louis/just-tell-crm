<?php

namespace App\Http\Props;

use App\Domain\Crm\Column;
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
            ['name' => Column::NEW, 'contacts' => []],
            ['name' => Column::QUALIFIED, 'contacts' => []],
            ['name' => Column::ESTIMATE_SENT, 'contacts' => []],
        ];

        foreach ($contacts as $contact) {
            foreach ($columns as $index => $column) {
                if ($column['name'] !== $contact->column || $contact->id === null) {
                    continue;
                }

                $target = self::target($contact->column);
                $columns[$index]['contacts'][] = [
                    'id' => $contact->id,
                    'name' => $contact->name,
                    'showUrl' => route('contacts.show', $contact->id),
                    'nextColumn' => $target === null ? null : $target['column'],
                    'moveUrl' => $target === null ? null : route('contacts.show', $contact->id).'#'.$target['anchor'],
                ];
            }
        }

        return [
            'columns' => $columns,
            'createUrl' => route('contacts.create'),
            'importUrl' => route('contacts.import'),
        ];
    }

    /**
     * The next column, and the contact-page anchor for the fields that move a card there.
     *
     * @return array{column: string, anchor: string}|null
     */
    private static function target(string $column): ?array
    {
        return match ($column) {
            Column::NEW => ['column' => Column::QUALIFIED, 'anchor' => 'qualify'],
            Column::QUALIFIED => ['column' => Column::ESTIMATE_SENT, 'anchor' => 'estimate'],
            default => null,
        };
    }
}
