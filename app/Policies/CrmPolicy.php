<?php

namespace App\Policies;

use App\Domain\Crm\Contact;
use App\Domain\Crm\User;

final class CrmPolicy
{
    public function participate(?User $actor): bool
    {
        return $actor !== null;
    }

    public function viewContact(?User $actor, Contact $contact): bool
    {
        return $actor !== null && $actor->id === $contact->userId;
    }

    public function selectActingUser(): bool
    {
        return true;
    }
}
