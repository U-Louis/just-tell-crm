<?php

namespace App\Domain\Crm;

final class UpdateContact
{
    public function __construct(private CrmRepository $crm) {}

    public function execute(Actor $actor, int $contactId, string $name, string $email, string $company): Contact
    {
        if ($actor->id < 1) {
            throw new CrmException('user.required', 'F13');
        }

        $contact = $this->crm->findContact($contactId);
        if ($contact === null) {
            throw new \RuntimeException('Contact is missing.');
        }

        $name = trim($name);
        if ($name === '') {
            throw new CrmException('contact.name.required', 'F14', [
                'name' => 'contact.name.required',
            ]);
        }

        $email = trim($email);
        $company = trim($company);

        return $this->crm->saveContact(new Contact(
            $contact->id,
            $contact->userId,
            $name,
            $email === '' ? null : $email,
            $company === '' ? null : $company,
            $contact->column,
            $contact->sentEmailBody,
            $contact->grandTotal,
        ));
    }
}
