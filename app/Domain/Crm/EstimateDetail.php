<?php

namespace App\Domain\Crm;

final class EstimateDetail
{
    /**
     * @param  list<EstimateLineDetail>  $lines
     */
    public function __construct(
        public readonly int $id,
        public readonly int $contactId,
        public readonly int $userId,
        public readonly string $contactName,
        public readonly array $lines,
        public readonly int $grandTotal,
    ) {}
}
