<?php

namespace App\Http\Props;

use App\Domain\Crm\Contact;
use App\Http\ListQuery;

final class ContactListPageProps
{
    /**
     * @param  list<Contact>  $contacts
     * @return array<string, mixed>
     */
    public static function make(array $contacts, int $count, int $page, string $term, bool $hasMore): array
    {
        return [
            'contacts' => array_map(function (Contact $contact) {
                return [
                    'id' => $contact->id,
                    'name' => $contact->name,
                    'column' => $contact->column,
                    'showUrl' => route('contacts.show', $contact->id),
                    'destroyUrl' => route('contacts.destroy', $contact->id),
                ];
            }, $contacts),
            'count' => $count,
            'page' => $page,
            'pageCount' => ListQuery::pageCount($count),
            'query' => $term,
            'previousUrl' => ListQuery::previousUrl('contacts.index', $page, $term),
            'nextUrl' => ListQuery::nextUrl('contacts.index', $page, $term, $hasMore),
            'csvUrl' => route('contacts.csv', $term === '' ? [] : ['q' => $term]),
            'createUrl' => route('contacts.create'),
            'urls' => [
                'index' => route('contacts.index'),
            ],
        ];
    }
}
