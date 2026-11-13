<?php

namespace App\Domain\Crm;

final class SentEmail
{
    public function __construct(
        public readonly int $contactId,
        public readonly string $to,
        public readonly string $body,
    ) {}
}
