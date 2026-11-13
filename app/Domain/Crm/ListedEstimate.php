<?php

namespace App\Domain\Crm;

final class ListedEstimate
{
    public function __construct(
        public readonly int $id,
        public readonly string $contactName,
        public readonly int $grandTotal,
    ) {}
}
