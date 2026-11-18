<?php

namespace App\Infrastructure\Persistence\Crm;

use App\Domain\Crm\Contact;
use App\Domain\Crm\CrmRepository;
use App\Domain\Crm\EstimateDetail;
use App\Domain\Crm\EstimateLineDetail;
use App\Domain\Crm\ListedEstimate;
use App\Domain\Crm\Product;
use App\Domain\Crm\User;
use App\Models\User as UserRecord;
use Illuminate\Database\Query\Builder;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

final class EloquentCrmRepository implements CrmRepository
{
    public function listUsers(): array
    {
        return UserRecord::query()
            ->orderBy('name')
            ->get()
            ->map(fn (UserRecord $user) => new User($user->id, $user->name))
            ->all();
    }

    public function findUser(int $id): ?User
    {
        $user = UserRecord::query()->find($id);

        return $user === null ? null : new User($user->id, $user->name);
    }

    public function createUser(string $name): User
    {
        $user = UserRecord::query()->create(['name' => $name]);

        return new User($user->id, $user->name);
    }

    public function saveUser(User $user): User
    {
        $record = UserRecord::query()->find($user->id);
        if ($record === null) {
            throw new \RuntimeException('User is missing.');
        }

        $record->fill(['name' => $user->name])->save();

        return new User($record->id, $record->name);
    }

    public function deleteUser(int $id): void
    {
        UserRecord::query()->whereKey($id)->delete();
    }

    public function userNameTaken(string $name, ?int $exceptId = null): bool
    {
        return UserRecord::query()
            ->where('name', $name)
            ->when($exceptId !== null, fn ($query) => $query->where('id', '!=', $exceptId))
            ->exists();
    }

    public function userOwnsContact(int $userId): bool
    {
        return ContactRecord::query()->where('user_id', $userId)->exists();
    }

    public function searchUsers(string $query, int $limit, int $offset): array
    {
        return $this->matchingName(UserRecord::query()->orderBy('name'), 'name', $query)
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->map(fn (UserRecord $user) => new User($user->id, $user->name))
            ->all();
    }

    public function countUsers(): int
    {
        return UserRecord::query()->count();
    }

    public function listContacts(int $userId): array
    {
        return ContactRecord::query()
            ->where('user_id', $userId)
            ->orderBy('id')
            ->get()
            ->map(fn (ContactRecord $record) => $this->summary($record))
            ->all();
    }

    public function listEstimates(int $userId): array
    {
        return DB::table('estimates')
            ->join('contacts', 'contacts.id', '=', 'estimates.contact_id')
            ->where('contacts.user_id', $userId)
            ->orderBy('estimates.id')
            ->get([
                'estimates.id',
                'contacts.name as contact_name',
                'estimates.grand_total',
            ])
            ->map(fn (object $row) => new ListedEstimate(
                (int) $row->id,
                (string) $row->contact_name,
                (int) $row->grand_total,
            ))
            ->all();
    }

    public function searchContacts(int $userId, string $query, int $limit, int $offset): array
    {
        return $this->matchingName(
            ContactRecord::query()->where('user_id', $userId)->orderBy('id'),
            'name',
            $query,
        )
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->map(fn (ContactRecord $record) => $this->summary($record))
            ->all();
    }

    public function countContacts(int $userId): int
    {
        return ContactRecord::query()->where('user_id', $userId)->count();
    }

    public function searchEstimates(int $userId, string $query, int $limit, int $offset): array
    {
        return $this->matchingName(
            $this->estimatesFor($userId),
            'contacts.name',
            $query,
        )
            ->offset($offset)
            ->limit($limit)
            ->get([
                'estimates.id',
                'contacts.name as contact_name',
                'estimates.grand_total',
            ])
            ->map(fn (object $row) => new ListedEstimate(
                (int) $row->id,
                (string) $row->contact_name,
                (int) $row->grand_total,
            ))
            ->all();
    }

    public function countEstimates(int $userId): int
    {
        return $this->estimatesFor($userId)->count();
    }

    public function findContact(int $id): ?Contact
    {
        $record = ContactRecord::query()->find($id);

        return $record === null ? null : $this->detail($record);
    }

    public function addContact(Contact $contact): Contact
    {
        $record = ContactRecord::query()->create([
            'user_id' => $contact->userId,
            'name' => $contact->name,
            'email' => $contact->email,
            'company' => $contact->company,
            'column' => $contact->column,
        ]);

        return $this->summary($record);
    }

    public function saveContact(Contact $contact): Contact
    {
        $record = ContactRecord::query()->find($contact->id);
        if ($record === null) {
            throw new \RuntimeException('Contact is missing.');
        }

        $record->fill([
            'name' => $contact->name,
            'email' => $contact->email,
            'company' => $contact->company,
            'column' => $contact->column,
        ])->save();

        return $this->detail($record);
    }

    public function deleteContact(int $id): void
    {
        DB::transaction(function () use ($id): void {
            $estimateIds = EstimateRecord::query()->where('contact_id', $id)->pluck('id');
            EstimateLineRecord::query()->whereIn('estimate_id', $estimateIds)->delete();
            EstimateRecord::query()->where('contact_id', $id)->delete();
            SentEmailRecord::query()->where('contact_id', $id)->delete();
            ContactRecord::query()->whereKey($id)->delete();
        });
    }

    public function listProducts(): array
    {
        return ProductRecord::query()
            ->orderBy('name')
            ->get()
            ->map(fn (ProductRecord $product) => new Product($product->id, $product->name, (int) $product->unit_price))
            ->all();
    }

    public function findProduct(int $id): ?Product
    {
        $product = ProductRecord::query()->find($id);

        return $product === null ? null : new Product($product->id, $product->name, (int) $product->unit_price);
    }

    public function searchProducts(string $query, int $limit, int $offset): array
    {
        return $this->matchingName(ProductRecord::query()->orderBy('name'), 'name', $query)
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->map(fn (ProductRecord $product) => new Product($product->id, $product->name, (int) $product->unit_price))
            ->all();
    }

    public function countProducts(): int
    {
        return ProductRecord::query()->count();
    }

    public function createProduct(string $name, int $unitPrice): Product
    {
        $product = ProductRecord::query()->create([
            'name' => $name,
            'unit_price' => $unitPrice,
        ]);

        return new Product($product->id, $product->name, (int) $product->unit_price);
    }

    public function saveProduct(Product $product): Product
    {
        $record = ProductRecord::query()->find($product->id);
        if ($record === null) {
            throw new \RuntimeException('Product is missing.');
        }

        $record->fill([
            'name' => $product->name,
            'unit_price' => $product->unitPrice,
        ])->save();

        return new Product($record->id, $record->name, (int) $record->unit_price);
    }

    public function deleteProduct(int $id): void
    {
        ProductRecord::query()->whereKey($id)->delete();
    }

    public function productNameTaken(string $name, ?int $exceptId = null): bool
    {
        return ProductRecord::query()
            ->where('name', $name)
            ->when($exceptId !== null, fn ($query) => $query->where('id', '!=', $exceptId))
            ->exists();
    }

    public function productOnEstimateLine(int $productId): bool
    {
        return EstimateLineRecord::query()->where('product_id', $productId)->exists();
    }

    public function findEstimate(int $id): ?EstimateDetail
    {
        $estimate = EstimateRecord::query()->find($id);
        if ($estimate === null) {
            return null;
        }

        $contact = ContactRecord::query()->find($estimate->contact_id);
        if ($contact === null) {
            return null;
        }

        $storedLines = EstimateLineRecord::query()->where('estimate_id', $estimate->id)->orderBy('id')->get();
        $products = ProductRecord::query()
            ->whereIn('id', $storedLines->pluck('product_id'))
            ->get()
            ->keyBy('id');

        $lines = [];
        foreach ($storedLines as $line) {
            $product = $products->get($line->product_id);
            $lines[] = new EstimateLineDetail(
                (int) $line->product_id,
                $product === null ? '' : $product->name,
                (int) $line->quantity,
                (int) $line->unit_price,
                $product === null ? (int) $line->unit_price : (int) $product->unit_price,
            );
        }

        return new EstimateDetail(
            $estimate->id,
            $contact->id,
            (int) $contact->user_id,
            $contact->name,
            $lines,
            (int) $estimate->grand_total,
        );
    }

    public function replaceEstimateLines(int $estimateId, array $lines, int $grandTotal): void
    {
        DB::transaction(function () use ($estimateId, $lines, $grandTotal): void {
            $estimate = EstimateRecord::query()->find($estimateId);
            if ($estimate === null) {
                throw new \RuntimeException('Estimate is missing.');
            }

            EstimateLineRecord::query()->where('estimate_id', $estimateId)->delete();
            foreach ($lines as $line) {
                EstimateLineRecord::query()->create([
                    'estimate_id' => $estimateId,
                    'product_id' => $line->productId,
                    'quantity' => $line->quantity,
                    'unit_price' => $line->unitPrice,
                ]);
            }

            $estimate->fill(['grand_total' => $grandTotal])->save();
        });
    }

    public function deleteEstimate(int $id): void
    {
        DB::transaction(function () use ($id): void {
            EstimateLineRecord::query()->where('estimate_id', $id)->delete();
            EstimateRecord::query()->whereKey($id)->delete();
        });
    }

    public function recordEstimateSend(Contact $contact, array $lines, int $grandTotal, string $to, string $body): Contact
    {
        return DB::transaction(function () use ($contact, $lines, $grandTotal, $to, $body) {
            $estimate = EstimateRecord::query()->create([
                'contact_id' => $contact->id,
                'grand_total' => $grandTotal,
            ]);

            foreach ($lines as $line) {
                EstimateLineRecord::query()->create([
                    'estimate_id' => $estimate->id,
                    'product_id' => $line->productId,
                    'quantity' => $line->quantity,
                    'unit_price' => $line->unitPrice,
                ]);
            }

            SentEmailRecord::query()->create([
                'contact_id' => $contact->id,
                'to_address' => $to,
                'body' => $body,
            ]);

            Mail::raw($body, function (Message $message) use ($to): void {
                $message->to($to)->subject('Estimate');
            });

            ContactRecord::query()->whereKey($contact->id)->update([
                'column' => $contact->column,
            ]);

            $stored = $this->findContact((int) $contact->id);
            if ($stored === null) {
                throw new \RuntimeException('Contact is missing.');
            }

            return $stored;
        });
    }

    private function estimatesFor(int $userId): Builder
    {
        return DB::table('estimates')
            ->join('contacts', 'contacts.id', '=', 'estimates.contact_id')
            ->where('contacts.user_id', $userId)
            ->orderBy('estimates.id');
    }

    /**
     * @template T of \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     *
     * @param  T  $query
     * @return T
     */
    private function matchingName($query, string $column, string $term)
    {
        $term = trim($term);
        if ($term === '') {
            return $query;
        }

        return $query->whereLike($column, '%'.addcslashes($term, '%_\\').'%');
    }

    private function summary(ContactRecord $record): Contact
    {
        return new Contact(
            $record->id,
            (int) $record->user_id,
            $record->name,
            $record->email,
            $record->company,
            $record->column,
        );
    }

    private function detail(ContactRecord $record): Contact
    {
        $body = SentEmailRecord::query()->where('contact_id', $record->id)->latest('id')->value('body');
        $total = EstimateRecord::query()->where('contact_id', $record->id)->latest('id')->value('grand_total');

        return new Contact(
            $record->id,
            (int) $record->user_id,
            $record->name,
            $record->email,
            $record->company,
            $record->column,
            is_string($body) ? $body : null,
            is_numeric($total) ? (int) $total : null,
        );
    }
}
