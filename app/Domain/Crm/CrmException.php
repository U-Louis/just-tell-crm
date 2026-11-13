<?php

namespace App\Domain\Crm;

use RuntimeException;

final class CrmException extends RuntimeException
{
    /**
     * @param  array<string, string>|null  $fields
     */
    public function __construct(
        public readonly string $errorCode,
        public readonly string $featureId,
        public readonly ?array $fields = null,
    ) {
        parent::__construct($errorCode);
    }
}
