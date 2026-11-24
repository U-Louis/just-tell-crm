<?php

namespace App\Http\Props;

use App\Domain\Crm\ProductCategory;

final class ProductCreatePageProps
{
    /**
     * @return array<string, mixed>
     */
    public static function make(): array
    {
        return [
            'categories' => ProductCategory::VALUES,
            'urls' => [
                'store' => route('products.store'),
            ],
        ];
    }
}
