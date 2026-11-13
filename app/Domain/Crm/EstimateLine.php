<?php

namespace App\Domain\Crm;

final class EstimateLine
{
    public function __construct(
        public readonly int $productId,
        public readonly int $quantity,
        public readonly int $unitPrice,
    ) {}
}
