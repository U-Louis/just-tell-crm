<?php

namespace App\Domain\Crm;

final class DeleteUser
{
    public function __construct(private CrmRepository $crm) {}

    public function execute(Actor $actor, int $userId): void
    {
        if ($actor->id < 1) {
            throw new CrmException('user.required', 'F12');
        }

        $user = $this->crm->findUser($userId);
        if ($user === null) {
            throw new \RuntimeException('User is missing.');
        }

        if ($this->crm->userOwnsContact($userId)) {
            throw new CrmException('user.delete.blocked', 'F11');
        }

        $this->crm->deleteUser($userId);
    }
}
