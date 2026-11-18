<?php

namespace App\Domain\Crm;

final class UpdateUser
{
    public function __construct(private CrmRepository $crm) {}

    public function execute(Actor $actor, int $userId, string $name): User
    {
        if ($actor->id < 1) {
            throw new CrmException('user.required', 'F10');
        }

        $user = $this->crm->findUser($userId);
        if ($user === null) {
            throw new \RuntimeException('User is missing.');
        }

        $name = trim($name);
        if ($name === '') {
            throw new CrmException('user.name.required', 'F8', [
                'name' => 'user.name.required',
            ]);
        }

        if ($this->crm->userNameTaken($name, $userId)) {
            throw new CrmException('user.name.taken', 'F9', [
                'name' => 'user.name.taken',
            ]);
        }

        return $this->crm->saveUser(new User($userId, $name));
    }
}
