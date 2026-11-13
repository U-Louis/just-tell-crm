<?php

namespace App\Domain\Crm;

final class CreateContact
{
    public function __construct(private CrmRepository $crm) {}

    public function execute(Actor $actor, string $name): Contact
    {
        if ($actor->id < 1) {
            throw new CrmException('user.required', 'F1');
        }

        $name = trim($name);
        if ($name === '') {
            throw new CrmException('contact.name.required', 'F2', [
                'name' => 'contact.name.required',
            ]);
        }

        return $this->crm->addContact(new Contact(
            null,
            $actor->id,
            $name,
            null,
            null,
            Column::NEW,
        ));
    }
}
