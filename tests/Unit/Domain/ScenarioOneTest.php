<?php

namespace Tests\Unit\Domain;

use App\Domain\Crm\Actor;
use App\Domain\Crm\Contact;
use App\Domain\Crm\CreateContact;
use App\Domain\Crm\CrmException;
use App\Domain\Crm\Product;
use App\Domain\Crm\QualifyContact;
use App\Domain\Crm\SendEstimate;
use PHPUnit\Framework\TestCase;

class ScenarioOneTest extends TestCase
{
    public function test_f1_create_stores_name_and_column_new(): void
    {
        $repository = new InMemoryCrmRepository;
        $action = new CreateContact($repository);

        $contact = $action->execute(new Actor(1), 'Northwind');

        $this->assertSame('Northwind', $contact->name);
        $this->assertSame(1, $contact->userId);
        $this->assertSame('new', $contact->column);
        $this->assertCount(1, $repository->listContacts(1));
        $this->assertSame([], $repository->listContacts(2));
    }

    public function test_f2_empty_name_writes_nothing(): void
    {
        $repository = new InMemoryCrmRepository;
        $action = new CreateContact($repository);

        try {
            $action->execute(new Actor(1), '');
            $this->fail('Expected contact.name.required');
        } catch (CrmException $exception) {
            $this->assertSame('contact.name.required', $exception->errorCode);
            $this->assertSame('F2', $exception->featureId);
        }

        $this->assertSame([], $repository->listContacts(1));
    }

    public function test_f3_qualify_sets_email_company_and_column_qualified(): void
    {
        $repository = new InMemoryCrmRepository;
        $contact = (new CreateContact($repository))->execute(new Actor(1), 'Northwind');

        $qualified = (new QualifyContact($repository))->execute(
            new Actor(1),
            (int) $contact->id,
            'northwind@example.com',
            'Northwind',
        );

        $this->assertSame('northwind@example.com', $qualified->email);
        $this->assertSame('Northwind', $qualified->company);
        $this->assertSame('qualified', $qualified->column);
        $this->assertTrue($qualified->validated);
        $this->assertSame(1, $qualified->userId);
    }

    public function test_f4_send_sets_estimate_sent_and_records_the_test_body(): void
    {
        $repository = $this->repositoryWithProducts();
        $contact = (new CreateContact($repository))->execute(new Actor(1), 'Northwind');
        $repository->saveContact(new Contact(
            $contact->id,
            $contact->userId,
            'Northwind',
            'northwind@example.com',
            'Northwind',
            'qualified',
            validated: true,
        ));

        $sent = (new SendEstimate($repository))->execute(new Actor(1), (int) $contact->id, [
            ['product_id' => 1, 'quantity' => 2],
            ['product_id' => 2, 'quantity' => 1],
        ]);

        $this->assertSame('estimate sent', $sent->column);
        $this->assertSame(25, $sent->grandTotal);
        $this->assertSame('This is a test.', $sent->sentEmailBody);
        $this->assertCount(1, $repository->sentEmails());
        $this->assertSame('northwind@example.com', $repository->sentEmails()[0]->to);
        $this->assertSame('This is a test.', $repository->sentEmails()[0]->body);
        $this->assertSame(25, $repository->estimates()[0]->grandTotal);
    }

    public function test_f5_send_without_email_writes_nothing_and_leaves_qualified(): void
    {
        $repository = $this->repositoryWithProducts();
        $contact = (new CreateContact($repository))->execute(new Actor(1), 'Northwind');
        $repository->saveContact(new Contact(
            $contact->id,
            $contact->userId,
            'Northwind',
            null,
            'Northwind',
            'qualified',
            validated: true,
        ));

        try {
            (new SendEstimate($repository))->execute(new Actor(1), (int) $contact->id, [
                ['product_id' => 1, 'quantity' => 2],
                ['product_id' => 2, 'quantity' => 1],
            ]);
            $this->fail('Expected contact.email.required');
        } catch (CrmException $exception) {
            $this->assertSame('contact.email.required', $exception->errorCode);
            $this->assertSame('F5', $exception->featureId);
        }

        $stored = $repository->findContact((int) $contact->id);
        $this->assertNotNull($stored);
        $this->assertSame('qualified', $stored->column);
        $this->assertSame([], $repository->sentEmails());
        $this->assertSame([], $repository->estimates());
    }

    public function test_send_before_the_contact_is_validated_writes_nothing(): void
    {
        $repository = $this->repositoryWithProducts();
        $contact = (new CreateContact($repository))->execute(new Actor(1), 'Northwind');
        $repository->saveContact(new Contact(
            $contact->id,
            $contact->userId,
            'Northwind',
            'northwind@example.com',
            'Northwind',
            'new',
        ));

        try {
            (new SendEstimate($repository))->execute(new Actor(1), (int) $contact->id, [
                ['product_id' => 1, 'quantity' => 2],
            ]);
            $this->fail('Expected contact.not_validated');
        } catch (CrmException $exception) {
            $this->assertSame('contact.not_validated', $exception->errorCode);
            $this->assertSame('F4', $exception->featureId);
        }

        $stored = $repository->findContact((int) $contact->id);
        $this->assertNotNull($stored);
        $this->assertFalse($stored->validated);
        $this->assertSame('new', $stored->column);
        $this->assertSame([], $repository->sentEmails());
        $this->assertSame([], $repository->estimates());
    }

    public function test_a_later_send_leaves_the_column_estimate_sent(): void
    {
        $repository = $this->repositoryWithProducts();
        $contact = (new CreateContact($repository))->execute(new Actor(1), 'Northwind');
        $repository->saveContact(new Contact(
            $contact->id,
            $contact->userId,
            'Northwind',
            'northwind@example.com',
            'Northwind',
            'qualified',
            validated: true,
        ));
        $action = new SendEstimate($repository);
        $lines = [
            ['product_id' => 1, 'quantity' => 2],
            ['product_id' => 2, 'quantity' => 1],
        ];

        $action->execute(new Actor(1), (int) $contact->id, $lines);
        $again = $action->execute(new Actor(1), (int) $contact->id, $lines);

        $this->assertSame('estimate sent', $again->column);
        $this->assertSame('This is a test.', $again->sentEmailBody);
        $this->assertCount(2, $repository->sentEmails());
    }

    public function test_lists_keep_ada_contacts_and_estimates_off_ben(): void
    {
        $repository = $this->repositoryWithProducts();
        $contact = (new CreateContact($repository))->execute(new Actor(1), 'Northwind');
        $repository->saveContact(new Contact(
            $contact->id,
            $contact->userId,
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

        $this->assertSame(['Northwind'], array_map(fn (Contact $item) => $item->name, $repository->listContacts(1)));
        $this->assertSame([], $repository->listContacts(2));
        $this->assertSame('Northwind', $repository->listEstimates(1)[0]->contactName);
        $this->assertSame(25, $repository->listEstimates(1)[0]->grandTotal);
        $this->assertSame([], $repository->listEstimates(2));
        $this->assertSame(['Cable', 'Lamp'], array_map(fn (Product $product) => $product->name, $repository->listProducts()));
    }

    private function repositoryWithProducts(): InMemoryCrmRepository
    {
        $repository = new InMemoryCrmRepository;
        $repository->addProduct(new Product(1, 'Cable', 10));
        $repository->addProduct(new Product(2, 'Lamp', 5));

        return $repository;
    }
}
