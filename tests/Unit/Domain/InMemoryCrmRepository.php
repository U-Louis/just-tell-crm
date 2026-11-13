<?php

namespace Tests\Unit\Domain;

use App\Domain\Crm\Contact;
use App\Domain\Crm\CrmRepository;
use App\Domain\Crm\Estimate;
use App\Domain\Crm\Product;
use App\Domain\Crm\SentEmail;
use App\Domain\Crm\User;
use RuntimeException;

final class InMemoryCrmRepository implements CrmRepository
{
    /** @var array<int, User> */
    private array $users = [];

    /** @var array<int, Contact> */
    private array $contacts = [];

    /** @var array<int, Product> */
    private array $products = [];

    /** @var list<SentEmail> */
    private array $sent = [];

    /** @var list<Estimate> */
    private array $storedEstimates = [];

    public function addUser(User $user): void
    {
        $this->users[$user->id] = $user;
    }

    public function addProduct(Product $product): void
    {
        $this->products[$product->id] = $product;
    }

    public function listUsers(): array
    {
        return array_values($this->users);
    }

    public function findUser(int $id): ?User
    {
        return $this->users[$id] ?? null;
    }

    public function listContacts(): array
    {
        return array_values($this->contacts);
    }

    public function findContact(int $id): ?Contact
    {
        return $this->contacts[$id] ?? null;
    }

    public function addContact(Contact $contact): Contact
    {
        $id = $this->contacts === [] ? 1 : max(array_keys($this->contacts)) + 1;
        $stored = new Contact(
            $id,
            $contact->name,
            $contact->email,
            $contact->company,
            $contact->column,
            $contact->sentEmailBody,
            $contact->grandTotal,
        );
        $this->contacts[$id] = $stored;

        return $stored;
    }

    public function saveContact(Contact $contact): Contact
    {
        if ($contact->id === null || ! isset($this->contacts[$contact->id])) {
            throw new RuntimeException('Contact is missing.');
        }

        $this->contacts[$contact->id] = $contact;

        return $contact;
    }

    public function listProducts(): array
    {
        return array_values($this->products);
    }

    public function findProduct(int $id): ?Product
    {
        return $this->products[$id] ?? null;
    }

    public function recordEstimateSend(Contact $contact, array $lines, int $grandTotal, string $to, string $body): Contact
    {
        $this->storedEstimates[] = new Estimate((int) $contact->id, $lines, $grandTotal);
        $this->sent[] = new SentEmail((int) $contact->id, $to, $body);
        $stored = new Contact(
            $contact->id,
            $contact->name,
            $contact->email,
            $contact->company,
            $contact->column,
            $body,
            $grandTotal,
        );
        $this->contacts[(int) $contact->id] = $stored;

        return $stored;
    }

    /**
     * @return list<SentEmail>
     */
    public function sentEmails(): array
    {
        return $this->sent;
    }

    /**
     * @return list<Estimate>
     */
    public function estimates(): array
    {
        return $this->storedEstimates;
    }
}
