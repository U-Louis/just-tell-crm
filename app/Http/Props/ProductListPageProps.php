<?php

namespace App\Http\Props;

use App\Domain\Crm\Product;
use App\Http\ListQuery;

final class ProductListPageProps
{
    /**
     * @param  list<Product>  $products
     * @return array<string, mixed>
     */
    public static function make(array $products, int $count, int $page, string $term, bool $hasMore): array
    {
        return [
            'products' => array_map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'unitPrice' => $product->unitPrice,
                'showUrl' => route('products.show', $product->id),
            ], $products),
            'count' => $count,
            'page' => $page,
            'pageCount' => ListQuery::pageCount($count),
            'query' => $term,
            'previousUrl' => ListQuery::previousUrl('products.index', $page, $term),
            'nextUrl' => ListQuery::nextUrl('products.index', $page, $term, $hasMore),
            'csvUrl' => route('products.csv', $term === '' ? [] : ['q' => $term]),
            'createUrl' => route('products.create'),
            'importUrl' => route('products.import'),
            'urls' => [
                'index' => route('products.index'),
            ],
        ];
    }
}
