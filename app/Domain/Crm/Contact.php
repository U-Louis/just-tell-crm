<?php

namespace App\Domain\Crm;

final class Contact
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $name,
        public readonly ?string $email,
        public readonly ?string $company,
        public readonly string $column,
        public readonly ?string $sentEmailBody = null,
        public readonly ?int $grandTotal = null,
    ) {}

    public function qualify(string $email, string $company, string $column): self
    {
        return new self(
            $this->id,
            $this->name,
            $email === '' ? null : $email,
            $company === '' ? null : $company,
            $column,
            $this->sentEmailBody,
            $this->grandTotal,
        );
    }
}
