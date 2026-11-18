<?php

namespace App\Domain\Crm;

interface CrmRepository
{
    /**
     * @return list<User>
     */
    public function listUsers(): array;

    public function findUser(int $id): ?User;

    public function createUser(string $name): User;

    public function saveUser(User $user): User;

    public function deleteUser(int $id): void;

    public function userNameTaken(string $name, ?int $exceptId = null): bool;

    public function userOwnsContact(int $userId): bool;

    /**
     * @return list<User>
     */
    public function searchUsers(string $query, int $limit, int $offset): array;

    public function countUsers(): int;

    /**
     * @return list<Contact>
     */
    public function listContacts(int $userId): array;

    /**
     * @return list<Contact>
     */
    public function searchContacts(int $userId, string $query, int $limit, int $offset): array;

    public function countContacts(int $userId): int;

    /**
     * @return list<ListedEstimate>
     */
    public function listEstimates(int $userId): array;

    /**
     * @return list<ListedEstimate>
     */
    public function searchEstimates(int $userId, string $query, int $limit, int $offset): array;

    public function countEstimates(int $userId): int;

    public function findContact(int $id): ?Contact;

    public function addContact(Contact $contact): Contact;

    public function saveContact(Contact $contact): Contact;

    public function deleteContact(int $id): void;

    /**
     * @return list<Product>
     */
    public function listProducts(): array;

    /**
     * @return list<Product>
     */
    public function searchProducts(string $query, int $limit, int $offset): array;

    public function countProducts(): int;

    public function findProduct(int $id): ?Product;

    public function createProduct(string $name, int $unitPrice): Product;

    public function saveProduct(Product $product): Product;

    public function deleteProduct(int $id): void;

    public function productNameTaken(string $name, ?int $exceptId = null): bool;

    public function productOnEstimateLine(int $productId): bool;

    public function findEstimate(int $id): ?EstimateDetail;

    /**
     * @param  list<EstimateLine>  $lines
     */
    public function replaceEstimateLines(int $estimateId, array $lines, int $grandTotal): void;

    public function deleteEstimate(int $id): void;

    /**
     * @param  list<EstimateLine>  $lines
     */
    public function recordEstimateSend(Contact $contact, array $lines, int $grandTotal, string $to, string $body): Contact;
}
