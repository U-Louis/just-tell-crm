<?php

namespace App\Http\Props;

use App\Domain\Crm\ListedEstimate;
use App\Http\ListQuery;

final class EstimateListPageProps
{
    /**
     * @param  list<ListedEstimate>  $estimates
     * @return array<string, mixed>
     */
    public static function make(array $estimates, int $count, int $page, string $term, bool $hasMore): array
    {
        return [
            'estimates' => array_map(fn (ListedEstimate $estimate) => [
                'id' => $estimate->id,
                'contactName' => $estimate->contactName,
                'grandTotal' => $estimate->grandTotal,
                'showUrl' => route('estimates.show', $estimate->id),
                'destroyUrl' => route('estimates.destroy', $estimate->id),
            ], $estimates),
            'count' => $count,
            'page' => $page,
            'pageCount' => ListQuery::pageCount($count),
            'query' => $term,
            'previousUrl' => ListQuery::previousUrl('estimates.index', $page, $term),
            'nextUrl' => ListQuery::nextUrl('estimates.index', $page, $term, $hasMore),
            'csvUrl' => route('estimates.csv', $term === '' ? [] : ['q' => $term]),
            'createUrl' => route('estimates.create'),
            'importUrl' => route('estimates.import'),
            'urls' => [
                'index' => route('estimates.index'),
            ],
        ];
    }
}
