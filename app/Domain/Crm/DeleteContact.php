<?php

namespace App\Domain\Crm;

final class DeleteContact
{
    public function __construct(private CrmRepository $crm) {}

    public function execute(Actor $actor, int $contactId): void
    {
        if ($actor->id < 1) {
            throw new CrmException('user.required', 'F15');
        }

        $contact = $this->crm->findContact($contactId);
        if ($contact === null) {
            throw new \RuntimeException('Contact is missing.');
        }

        $this->crm->deleteContact($contactId);
    }
}
