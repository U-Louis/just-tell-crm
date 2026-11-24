<?php

namespace App\Http\Props;

use App\Domain\Crm\User;
use App\Http\ListQuery;

final class UserListPageProps
{
    /**
     * @param  list<User>  $users
     * @return array<string, mixed>
     */
    public static function make(array $users, int $count, int $page, string $term, bool $hasMore): array
    {
        return [
            'users' => array_map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'showUrl' => route('users.show', $user->id),
                'destroyUrl' => route('users.destroy', $user->id),
            ], $users),
            'count' => $count,
            'page' => $page,
            'pageCount' => ListQuery::pageCount($count),
            'query' => $term,
            'previousUrl' => ListQuery::previousUrl('users.index', $page, $term),
            'nextUrl' => ListQuery::nextUrl('users.index', $page, $term, $hasMore),
            'csvUrl' => route('users.csv', $term === '' ? [] : ['q' => $term]),
            'createUrl' => route('users.create'),
            'urls' => [
                'index' => route('users.index'),
            ],
        ];
    }
}
