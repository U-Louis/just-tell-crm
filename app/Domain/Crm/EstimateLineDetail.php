<?php

namespace App\Domain\Crm;

final class EstimateLineDetail
{
    public function __construct(
        public readonly int $productId,
        public readonly string $productName,
        public readonly int $quantity,
        public readonly int $unitPrice,
        public readonly int $currentUnitPrice,
    ) {}
}
