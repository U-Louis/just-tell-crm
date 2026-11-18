<?php

namespace App\Domain\Crm;

final class CreateUser
{
    public function __construct(private CrmRepository $crm) {}

    public function execute(Actor $actor, string $name): User
    {
        if ($actor->id < 1) {
            throw new CrmException('user.required', 'F7');
        }

        $name = trim($name);
        if ($name === '') {
            throw new CrmException('user.name.required', 'F8', [
                'name' => 'user.name.required',
            ]);
        }

        if ($this->crm->userNameTaken($name)) {
            throw new CrmException('user.name.taken', 'F9', [
                'name' => 'user.name.taken',
            ]);
        }

        return $this->crm->createUser($name);
    }
}
