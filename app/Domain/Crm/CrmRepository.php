<?php

namespace App\Domain\Crm;

interface CrmRepository
{
    /**
     * @return list<User>
     */
    public function listUsers(): array;

    public function findUser(int $id): ?User;

    /**
     * @return list<Contact>
     */
    public function listContacts(int $userId): array;

    /**
     * @return list<ListedEstimate>
     */
    public function listEstimates(int $userId): array;

    public function findContact(int $id): ?Contact;

    public function addContact(Contact $contact): Contact;

    public function saveContact(Contact $contact): Contact;

    /**
     * @return list<Product>
     */
    public function listProducts(): array;

    public function findProduct(int $id): ?Product;

    /**
     * @param  list<EstimateLine>  $lines
     */
    public function recordEstimateSend(Contact $contact, array $lines, int $grandTotal, string $to, string $body): Contact;
}
