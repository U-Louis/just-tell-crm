<?php

namespace App\Http;

use Illuminate\Http\Request;

final class ListQuery
{
    public const SIZE = 15;

    public const EXPORT_LIMIT = 100000;

    public static function page(Request $request): int
    {
        $page = $request->query('page', 1);
        if (is_array($page) || ! is_numeric($page)) {
            return 1;
        }

        return max(1, (int) $page);
    }

    public static function term(Request $request): string
    {
        $term = $request->query('q', '');
        if (! is_string($term)) {
            return '';
        }

        return trim($term);
    }

    public static function offset(int $page): int
    {
        return ($page - 1) * self::SIZE;
    }

    /**
     * @return array<string, int|string>
     */
    public static function parameters(int $page, string $term): array
    {
        $parameters = [];
        if ($page > 1) {
            $parameters['page'] = $page;
        }
        if ($term !== '') {
            $parameters['q'] = $term;
        }

        return $parameters;
    }

    public static function previousUrl(string $name, int $page, string $term): ?string
    {
        if ($page <= 1) {
            return null;
        }

        return route($name, self::parameters($page - 1, $term));
    }

    public static function nextUrl(string $name, int $page, string $term, bool $hasMore): ?string
    {
        if (! $hasMore) {
            return null;
        }

        return route($name, self::parameters($page + 1, $term));
    }
}
