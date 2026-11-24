<?php

namespace Tests\Unit\Domain;

use App\Domain\Crm\Actor;
use App\Domain\Crm\Contact;
use App\Domain\Crm\CreateContact;
use App\Domain\Crm\CreateProduct;
use App\Domain\Crm\CreateUser;
use App\Domain\Crm\CrmException;
use App\Domain\Crm\DeleteContact;
use App\Domain\Crm\DeleteEstimate;
use App\Domain\Crm\DeleteProduct;
use App\Domain\Crm\DeleteUser;
use App\Domain\Crm\Product;
use App\Domain\Crm\SendEstimate;
use App\Domain\Crm\UpdateContact;
use App\Domain\Crm\UpdateEstimate;
use App\Domain\Crm\UpdateProduct;
use App\Domain\Crm\User;
use PHPUnit\Framework\TestCase;

class DraftUseCasesTest extends TestCase
{
    public function test_user_name_refusals_and_blocked_delete(): void
    {
        $repository = new InMemoryCrmRepository;
        $repository->addUser(new User(1, 'Ada'));
        $create = new CreateUser($repository);

        try {
            $create->execute(new Actor(1), '  ');
            $this->fail('Expected user.name.required');
        } catch (CrmException $exception) {
            $this->assertSame('user.name.required', $exception->errorCode);
            $this->assertSame('F8', $exception->featureId);
        }

        try {
            $create->execute(new Actor(1), 'Ada');
            $this->fail('Expected user.name.taken');
        } catch (CrmException $exception) {
            $this->assertSame('user.name.taken', $exception->errorCode);
            $this->assertSame('F9', $exception->featureId);
        }

        $this->assertSame(['Ada'], array_map(fn (User $user) => $user->name, $repository->listUsers()));

        (new CreateContact($repository))->execute(new Actor(1), 'Northwind');

        try {
            (new DeleteUser($repository))->execute(new Actor(1), 1);
            $this->fail('Expected user.delete.blocked');
        } catch (CrmException $exception) {
            $this->assertSame('user.delete.blocked', $exception->errorCode);
            $this->assertSame('F11', $exception->featureId);
        }

        $this->assertNotNull($repository->findUser(1));
    }

    public function test_delete_user_with_no_contact_removes_the_user(): void
    {
        $repository = new InMemoryCrmRepository;
        $repository->addUser(new User(1, 'Ada'));
        $dana = (new CreateUser($repository))->execute(new Actor(1), 'Dana');

        (new DeleteUser($repository))->execute(new Actor(1), (int) $dana->id);

        $this->assertNull($repository->findUser((int) $dana->id));
        $this->assertNotNull($repository->findUser(1));
    }

    public function test_contact_update_keeps_the_column_and_delete_removes_children(): void
    {
        $repository = $this->sentRepository();
        $contact = $repository->findContact(1);
        $this->assertNotNull($contact);

        try {
            (new UpdateContact($repository))->execute(new Actor(1), 1, ' ', 'a@example.com', 'Co');
            $this->fail('Expected contact.name.required');
        } catch (CrmException $exception) {
            $this->assertSame('contact.name.required', $exception->errorCode);
            $this->assertSame('F14', $exception->featureId);
        }

        $updated = (new UpdateContact($repository))->execute(new Actor(1), 1, 'Northwind Traders', '', '');

        $this->assertSame('Northwind Traders', $updated->name);
        $this->assertNull($updated->email);
        $this->assertNull($updated->company);
        $this->assertSame('estimate sent', $updated->column);
        $this->assertSame('This is a test.', $updated->sentEmailBody);
        $this->assertCount(1, $repository->sentEmails());

        (new DeleteContact($repository))->execute(new Actor(1), 1);

        $this->assertNull($repository->findContact(1));
        $this->assertSame([], $repository->estimates());
        $this->assertSame([], $repository->sentEmails());
    }

    public function test_product_price_change_does_not_rewrite_stored_lines_and_blocked_delete(): void
    {
        $repository = $this->sentRepository();

        try {
            (new CreateProduct($repository))->execute(new Actor(1), '', 3);
            $this->fail('Expected product.name.required');
        } catch (CrmException $exception) {
            $this->assertSame('product.name.required', $exception->errorCode);
        }

        try {
            (new CreateProduct($repository))->execute(new Actor(1), 'Cable', 3);
            $this->fail('Expected product.name.taken');
        } catch (CrmException $exception) {
            $this->assertSame('product.name.taken', $exception->errorCode);
        }

        try {
            (new CreateProduct($repository))->execute(new Actor(1), 'Plug', -1);
            $this->fail('Expected product.unit_price.invalid');
        } catch (CrmException $exception) {
            $this->assertSame('product.unit_price.invalid', $exception->errorCode);
        }

        (new UpdateProduct($repository))->execute(new Actor(1), 1, 'Cable', 12);

        $stored = $repository->estimates()[0];
        $this->assertSame(25, $stored->grandTotal);
        $this->assertSame(10, $stored->lines[0]->unitPrice);
        $detail = $repository->findEstimate(1);
        $this->assertNotNull($detail);
        $this->assertSame(10, $detail->lines[0]->unitPrice);
        $this->assertSame(12, $detail->lines[0]->currentUnitPrice);

        try {
            (new DeleteProduct($repository))->execute(new Actor(1), 1);
            $this->fail('Expected product.delete.blocked');
        } catch (CrmException $exception) {
            $this->assertSame('product.delete.blocked', $exception->errorCode);
            $this->assertSame('F21', $exception->featureId);
        }

        $this->assertNotNull($repository->findProduct(1));
        $plug = (new CreateProduct($repository))->execute(new Actor(1), 'Plug', 0);
        (new DeleteProduct($repository))->execute(new Actor(1), $plug->id);
        $this->assertNull($repository->findProduct($plug->id));
    }

    public function test_estimate_update_recomputes_the_grand_total_and_leaves_the_column(): void
    {
        $repository = $this->sentRepository();
        (new UpdateProduct($repository))->execute(new Actor(1), 1, 'Cable', 12);

        try {
            (new UpdateEstimate($repository))->execute(new Actor(1), 1, [
                ['product_id' => 1, 'quantity' => 0],
                ['product_id' => 2, 'quantity' => 0],
            ]);
            $this->fail('Expected estimate.lines.required');
        } catch (CrmException $exception) {
            $this->assertSame('estimate.lines.required', $exception->errorCode);
            $this->assertSame('F23', $exception->featureId);
        }

        $this->assertSame(25, $repository->estimates()[0]->grandTotal);
        $this->assertSame('estimate sent', $repository->findContact(1)?->column);

        (new UpdateEstimate($repository))->execute(new Actor(1), 1, [
            ['product_id' => 1, 'quantity' => 1],
            ['product_id' => 2, 'quantity' => 0],
        ]);

        $updated = $repository->findEstimate(1);
        $this->assertNotNull($updated);
        $this->assertSame(12, $updated->grandTotal);
        $this->assertCount(1, $updated->lines);
        $this->assertSame(12, $updated->lines[0]->unitPrice);
        $this->assertSame('estimate sent', $repository->findContact(1)?->column);
        $this->assertCount(1, $repository->sentEmails());

        (new DeleteEstimate($repository))->execute(new Actor(1), 1);

        $this->assertNull($repository->findEstimate(1));
        $this->assertSame('estimate sent', $repository->findContact(1)?->column);
        $this->assertSame('This is a test.', $repository->sentEmails()[0]->body);
    }

    public function test_counts_ignore_search_and_search_matches_name_only(): void
    {
        $repository = new InMemoryCrmRepository;
        $repository->addUser(new User(1, 'Ada'));
        $repository->addUser(new User(2, 'Ben'));
        foreach (range(1, 16) as $index) {
            $repository->createUser(sprintf('User %02d', $index));
        }

        $this->assertSame(18, $repository->countUsers());
        $this->assertCount(15, $repository->searchUsers('', 15, 0));
        $this->assertCount(3, $repository->searchUsers('', 15, 15));
        $this->assertSame(['Ada'], array_map(fn (User $user) => $user->name, $repository->searchUsers('ada', 15, 0)));
        $this->assertSame(18, $repository->countUsers());

        $contact = (new CreateContact($repository))->execute(new Actor(1), 'Northwind');
        $repository->saveContact(new Contact(
            $contact->id,
            1,
            'Northwind',
            'hidden@example.com',
            'Northwind',
            'new',
        ));
        (new CreateContact($repository))->execute(new Actor(1), 'Other');
        (new CreateContact($repository))->execute(new Actor(2), 'Ben contact');

        $this->assertSame([], $repository->searchContacts(1, 'hidden@example.com', 15, 0));
        $this->assertSame(['Northwind'], array_map(
            fn (Contact $item) => $item->name,
            $repository->searchContacts(1, 'North', 15, 0),
        ));
        $this->assertSame(2, $repository->countContacts(1));
        $this->assertSame(1, $repository->countContacts(2));
    }

    public function test_missing_actor_is_refused(): void
    {
        $repository = new InMemoryCrmRepository;

        try {
            (new CreateUser($repository))->execute(new Actor(0), 'Dana');
            $this->fail('Expected user.required');
        } catch (CrmException $exception) {
            $this->assertSame('user.required', $exception->errorCode);
            $this->assertSame('F7', $exception->featureId);
        }

        $this->assertSame(0, $repository->countUsers());
    }

    private function sentRepository(): InMemoryCrmRepository
    {
        $repository = new InMemoryCrmRepository;
        $repository->addUser(new User(1, 'Ada'));
        $repository->addProduct(new Product(1, 'Cable', 10));
        $repository->addProduct(new Product(2, 'Lamp', 5));
        $contact = (new CreateContact($repository))->execute(new Actor(1), 'Northwind');
        $repository->saveContact(new Contact(
            $contact->id,
            1,
            'Northwind',
            'northwind@example.com',
            'Northwind',
            'qualified',
            validated: true,
        ));
        (new SendEstimate($repository))->execute(new Actor(1), (int) $contact->id, [
            ['product_id' => 1, 'quantity' => 2],
            ['product_id' => 2, 'quantity' => 1],
        ]);

        return $repository;
    }
}
