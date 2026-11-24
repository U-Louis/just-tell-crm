<?php

namespace App\Domain\Crm;

final class Estimate
{
    /**
     * @param  list<EstimateLine>  $lines
     */
    public function __construct(
        public readonly int $contactId,
        public readonly array $lines,
        public readonly int $grandTotal,
        public readonly ?string $sentAt = null,
    ) {}
}
