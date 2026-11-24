<?php

namespace App\Http\Props;

final class ProductCreatePageProps
{
    /**
     * @return array<string, mixed>
     */
    public static function make(): array
    {
        return [
            'urls' => [
                'store' => route('products.store'),
            ],
        ];
    }
}
