<?php

namespace App\Domain\Crm;

final class Contact
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $userId,
        public readonly string $name,
        public readonly ?string $email,
        public readonly ?string $company,
        public readonly string $column,
        public readonly ?string $sentEmailBody = null,
        public readonly ?int $grandTotal = null,
        public readonly ?string $description = null,
        public readonly ?string $notes = null,
        public readonly ?string $companySize = null,
        public readonly ?int $ownerId = null,
        public readonly bool $validated = false,
    ) {}

    public function qualify(string $email, string $company, string $column): self
    {
        return new self(
            $this->id,
            $this->userId,
            $this->name,
            $email === '' ? null : $email,
            $company === '' ? null : $company,
            $column,
            $this->sentEmailBody,
            $this->grandTotal,
            $this->description,
            $this->notes,
            $this->companySize,
            $this->ownerId,
            true,
        );
    }
}
