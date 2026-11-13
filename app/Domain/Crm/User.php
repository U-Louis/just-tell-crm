<?php

namespace App\Domain\Crm;

final class User
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
    ) {}
}
