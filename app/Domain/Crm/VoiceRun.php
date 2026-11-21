<?php

namespace App\Domain\Crm;

final class VoiceRun
{
    public function __construct(
        public readonly int $id,
        public readonly string $sessionId,
        public readonly int $actingUserId,
        public readonly string $transcript,
        public readonly string $status,
        public readonly ?string $result,
        public readonly bool $applied,
    ) {}
}
