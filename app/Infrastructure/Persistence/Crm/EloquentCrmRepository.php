<?php

namespace App\Infrastructure\Persistence\Crm;

use App\Domain\Crm\Contact;
use App\Domain\Crm\CrmRepository;
use App\Domain\Crm\ListedEstimate;
use App\Domain\Crm\Product;
use App\Domain\Crm\User;
use App\Models\User as UserRecord;
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
