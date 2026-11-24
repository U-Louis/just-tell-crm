<?php

namespace App\Domain\Crm;

final class UpdateContact
{
    public function __construct(private CrmRepository $crm) {}

    public function execute(
        Actor $actor,
        int $contactId,
        string $name,
        string $email,
        string $company,
        ?string $description = null,
        ?string $notes = null,
        ?string $companySize = null,
        ?int $ownerId = null,
    ): Contact {
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

        $ownerId = $ownerId ?? $contact->ownerId ?? $contact->userId;
        if ($ownerId !== $actor->id && $this->crm->findUser($ownerId) === null) {
            throw new CrmException('contact.owner.invalid', 'F13', [
                'owner_id' => 'contact.owner.invalid',
            ]);
        }

        return $this->crm->saveContact(new Contact(
            $contact->id,
            $contact->userId,
            $name,
            FieldText::optional($email, 255, 'contact.email.invalid', 'F13', 'email'),
            FieldText::optional($company, 255, 'contact.company.invalid', 'F13', 'company'),
            $contact->column,
            $contact->sentEmailBody,
            $contact->grandTotal,
            FieldText::optional($description, 255, 'contact.description.invalid', 'F13', 'description'),
            FieldText::optional($notes, 255, 'contact.notes.invalid', 'F13', 'notes'),
            CompanySize::optional($companySize, 'F13'),
            $ownerId,
            $contact->validated,
        ));
    }
}
