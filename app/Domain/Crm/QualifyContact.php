<?php

namespace App\Domain\Crm;

final class QualifyContact
{
    public function __construct(private CrmRepository $crm) {}

    public function execute(Actor $actor, int $contactId, string $email, string $company): Contact
    {
        if ($actor->id < 1) {
            throw new CrmException('user.required', 'F3');
        }

        $contact = $this->crm->findContact($contactId);
        if ($contact === null) {
            throw new \RuntimeException('Contact is missing.');
        }

        $column = $contact->column === Column::NEW ? Column::QUALIFIED : $contact->column;

        return $this->crm->saveContact($contact->qualify(trim($email), trim($company), $column));
    }
}
