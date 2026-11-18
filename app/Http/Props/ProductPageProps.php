<?php

namespace App\Http\Props;

use App\Domain\Crm\Product;

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
            ],
            'urls' => [
                'update' => route('products.update', $product->id),
                'destroy' => route('products.destroy', $product->id),
                'pdf' => route('products.pdf', $product->id),
            ],
        ];
    }
}
