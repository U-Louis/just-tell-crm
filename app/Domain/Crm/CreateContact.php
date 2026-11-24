<?php

namespace App\Domain\Crm;

final class CreateContact
{
    public function __construct(private CrmRepository $crm) {}

    public function execute(
        Actor $actor,
        string $name,
        ?string $email = null,
        ?string $company = null,
        ?string $description = null,
        ?string $notes = null,
        ?string $companySize = null,
        ?int $ownerId = null,
    ): Contact {
        if ($actor->id < 1) {
            throw new CrmException('user.required', 'F1');
        }

        $name = trim($name);
        if ($name === '') {
            throw new CrmException('contact.name.required', 'F2', [
                'name' => 'contact.name.required',
            ]);
        }

        $ownerId = $ownerId ?? $actor->id;
        if ($ownerId !== $actor->id && $this->crm->findUser($ownerId) === null) {
            throw new CrmException('contact.owner.invalid', 'F1', [
                'owner_id' => 'contact.owner.invalid',
            ]);
        }

        return $this->crm->addContact(new Contact(
            null,
            $actor->id,
            $name,
            FieldText::optional($email, 255, 'contact.email.invalid', 'F1', 'email'),
            FieldText::optional($company, 255, 'contact.company.invalid', 'F1', 'company'),
            Column::NEW,
            null,
            null,
            FieldText::optional($description, 255, 'contact.description.invalid', 'F1', 'description'),
            FieldText::optional($notes, 255, 'contact.notes.invalid', 'F1', 'notes'),
            CompanySize::optional($companySize, 'F1'),
            $ownerId,
        ));
    }
}
