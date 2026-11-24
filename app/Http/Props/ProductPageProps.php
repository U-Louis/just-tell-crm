<?php

namespace App\Http\Props;

use App\Domain\Crm\Product;
use App\Domain\Crm\ProductCategory;

final class ProductPageProps
{
    /**
     * @return array<string, mixed>
     */
    public static function make(Product $product): array
    {
        return [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'unitPrice' => $product->unitPrice,
                'description' => $product->description,
                'category' => $product->category,
            ],
            'categories' => ProductCategory::VALUES,
            'urls' => [
                'update' => route('products.update', $product->id),
                'destroy' => route('products.destroy', $product->id),
                'pdf' => route('products.pdf', $product->id),
            ],
        ];
    }
}
