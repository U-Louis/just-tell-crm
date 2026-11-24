<?php

namespace App\Domain\Crm\Fixtures;

use App\Domain\Crm\EstimateLine;
use App\Domain\Crm\Product;

final class EstimateFactory
{
    /**
     * @param  list<Product>  $products
     * @return list<EstimateLine>
     */
    public static function lines(array $products, int $count): array
    {
        $pool = $products;
        shuffle($pool);
        $chosen = array_slice($pool, 0, $count);
        $lines = [];
        foreach ($chosen as $product) {
            $lines[] = new EstimateLine($product->id, random_int(1, 3), $product->unitPrice);
        }

        return $lines;
    }

    /**
     * @param  list<EstimateLine>  $lines
     */
    public static function total(array $lines): int
    {
        $total = 0;
        foreach ($lines as $line) {
            $total += $line->quantity * $line->unitPrice;
        }

        return $total;
    }
}
