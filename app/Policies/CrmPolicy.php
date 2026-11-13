<?php

namespace App\Policies;

use App\Domain\Crm\User;

final class CrmPolicy
{
    public function participate(?User $actor): bool
    {
        return $actor !== null;
    }

    public function selectActingUser(): bool
    {
        return true;
    }
}
