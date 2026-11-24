<?php

namespace Tests\Unit\Domain;

use App\Domain\Crm\Contact;
use App\Domain\Crm\CrmRepository;
use App\Domain\Crm\Estimate;
use App\Domain\Crm\EstimateDetail;
use App\Domain\Crm\EstimateLineDetail;
use App\Domain\Crm\ListedEstimate;
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

    /** @var array<int, Estimate> */
    private array $storedEstimates = [];

    private int $nextEstimateId = 1;

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

    public function createUser(string $name): User
    {
        $id = $this->users === [] ? 1 : max(array_keys($this->users)) + 1;
        $user = new User($id, $name);
        $this->users[$id] = $user;

        return $user;
    }

    public function saveUser(User $user): User
    {
        if (! isset($this->users[$user->id])) {
            throw new RuntimeException('User is missing.');
        }

        $this->users[$user->id] = $user;

        return $user;
    }

    public function deleteUser(int $id): void
    {
        unset($this->users[$id]);
    }

    public function userNameTaken(string $name, ?int $exceptId = null): bool
    {
        foreach ($this->users as $user) {
            if ($user->name === $name && $user->id !== $exceptId) {
                return true;
            }
        }

        return false;
    }

    public function userOwnsContact(int $userId): bool
    {
        foreach ($this->contacts as $contact) {
            if ($contact->userId === $userId || $contact->ownerId === $userId) {
                return true;
            }
        }

        return false;
    }

    public function searchUsers(string $query, int $limit, int $offset): array
    {
        $users = array_values($this->users);
        usort($users, fn (User $left, User $right) => strcmp($left->name, $right->name));

        return array_slice($this->matching($users, $query, fn (User $user) => $user->name), $offset, $limit);
    }

    public function countUsers(): int
    {
        return count($this->users);
    }

    public function listContacts(int $userId): array
    {
        return array_values(array_filter(
            $this->contacts,
            fn (Contact $contact) => $contact->userId === $userId,
        ));
    }

    public function searchContacts(int $userId, string $query, int $limit, int $offset): array
    {
        $contacts = $this->listContacts($userId);

        return array_slice($this->matching($contacts, $query, fn (Contact $contact) => $contact->name), $offset, $limit);
    }

    public function countContacts(int $userId): int
    {
        return count($this->listContacts($userId));
    }

    public function listEstimates(int $userId): array
    {
        $listed = [];
        foreach ($this->storedEstimates as $id => $estimate) {
            $contact = $this->contacts[$estimate->contactId] ?? null;
            if ($contact === null || $contact->userId !== $userId) {
                continue;
            }

            $listed[] = new ListedEstimate($id, $contact->name, $estimate->grandTotal);
        }

        return $listed;
    }

    public function searchEstimates(int $userId, string $query, int $limit, int $offset): array
    {
        return array_slice(
            $this->matching($this->listEstimates($userId), $query, fn (ListedEstimate $estimate) => $estimate->contactName),
            $offset,
            $limit,
        );
    }

    public function countEstimates(int $userId): int
    {
        return count($this->listEstimates($userId));
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
            $contact->userId,
            $contact->name,
            $contact->email,
            $contact->company,
            $contact->column,
            $contact->sentEmailBody,
            $contact->grandTotal,
            $contact->description,
            $contact->notes,
            $contact->companySize,
            $contact->ownerId ?? $contact->userId,
            $contact->validated,
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

    public function deleteContact(int $id): void
    {
        unset($this->contacts[$id]);
        $this->storedEstimates = array_filter(
            $this->storedEstimates,
            fn (Estimate $estimate) => $estimate->contactId !== $id,
        );
        $this->sent = array_values(array_filter(
            $this->sent,
            fn (SentEmail $email) => $email->contactId !== $id,
        ));
    }

    public function listProducts(): array
    {
        return array_values($this->products);
    }

    public function searchProducts(string $query, int $limit, int $offset): array
    {
        $products = array_values($this->products);
        usort($products, fn (Product $left, Product $right) => strcmp($left->name, $right->name));

        return array_slice($this->matching($products, $query, fn (Product $product) => $product->name), $offset, $limit);
    }

    public function countProducts(): int
    {
        return count($this->products);
    }

    public function findProduct(int $id): ?Product
    {
        return $this->products[$id] ?? null;
    }

    public function createProduct(string $name, int $unitPrice, ?string $description = null, ?string $category = null): Product
    {
        $id = $this->products === [] ? 1 : max(array_keys($this->products)) + 1;
        $product = new Product($id, $name, $unitPrice, $description, $category);
        $this->products[$id] = $product;

        return $product;
    }

    public function saveProduct(Product $product): Product
    {
        if (! isset($this->products[$product->id])) {
            throw new RuntimeException('Product is missing.');
        }

        $this->products[$product->id] = $product;

        return $product;
    }

    public function deleteProduct(int $id): void
    {
        unset($this->products[$id]);
    }

    public function productNameTaken(string $name, ?int $exceptId = null): bool
    {
        foreach ($this->products as $product) {
            if ($product->name === $name && $product->id !== $exceptId) {
                return true;
            }
        }

        return false;
    }

    public function productOnEstimateLine(int $productId): bool
    {
        foreach ($this->storedEstimates as $estimate) {
            foreach ($estimate->lines as $line) {
                if ($line->productId === $productId) {
                    return true;
                }
            }
        }

        return false;
    }

    public function findEstimate(int $id): ?EstimateDetail
    {
        $estimate = $this->storedEstimates[$id] ?? null;
        if ($estimate === null) {
            return null;
        }

        $contact = $this->contacts[$estimate->contactId] ?? null;
        if ($contact === null) {
            return null;
        }

        $lines = [];
        foreach ($estimate->lines as $line) {
            $product = $this->products[$line->productId] ?? null;
            $lines[] = new EstimateLineDetail(
                $line->productId,
                $product === null ? '' : $product->name,
                $line->quantity,
                $line->unitPrice,
                $product === null ? $line->unitPrice : $product->unitPrice,
            );
        }

        return new EstimateDetail(
            $id,
            (int) $contact->id,
            $contact->userId,
            $contact->name,
            $lines,
            $estimate->grandTotal,
            $estimate->sentAt,
        );
    }

    public function replaceEstimateLines(int $estimateId, array $lines, int $grandTotal): void
    {
        $estimate = $this->storedEstimates[$estimateId] ?? null;
        if ($estimate === null) {
            throw new RuntimeException('Estimate is missing.');
        }

        $this->storedEstimates[$estimateId] = new Estimate($estimate->contactId, $lines, $grandTotal, $estimate->sentAt);
    }

    public function addEstimate(int $contactId, array $lines, int $grandTotal): EstimateDetail
    {
        $id = $this->nextEstimateId++;
        $this->storedEstimates[$id] = new Estimate($contactId, $lines, $grandTotal, gmdate('Y-m-d H:i:s'));
        $detail = $this->findEstimate($id);
        if ($detail === null) {
            throw new RuntimeException('Estimate is missing.');
        }

        return $detail;
    }

    public function deleteEstimate(int $id): void
    {
        unset($this->storedEstimates[$id]);
    }

    public function recordEstimateSend(Contact $contact, array $lines, int $grandTotal, string $to, string $body): Contact
    {
        $id = $this->nextEstimateId++;
        $this->storedEstimates[$id] = new Estimate((int) $contact->id, $lines, $grandTotal, gmdate('Y-m-d H:i:s'));
        $this->sent[] = new SentEmail((int) $contact->id, $to, $body);
        $stored = new Contact(
            $contact->id,
            $contact->userId,
            $contact->name,
            $contact->email,
            $contact->company,
            $contact->column,
            $body,
            $grandTotal,
            $contact->description,
            $contact->notes,
            $contact->companySize,
            $contact->ownerId ?? $contact->userId,
            $contact->validated,
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
        return array_values($this->storedEstimates);
    }

    /**
     * @template T
     *
     * @param  list<T>  $rows
     * @param  callable(T): string  $name
     * @return list<T>
     */
    private function matching(array $rows, string $query, callable $name): array
    {
        $query = trim($query);
        if ($query === '') {
            return $rows;
        }

        return array_values(array_filter(
            $rows,
            fn ($row) => mb_stripos($name($row), $query) !== false,
        ));
    }
}
