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
        $this->assertSame('new', $contact->column);
        $this->assertCount(1, $repository->listContacts());
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

        $this->assertSame([], $repository->listContacts());
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
    }

    public function test_f4_send_sets_estimate_sent_and_records_the_test_body(): void
    {
        $repository = $this->repositoryWithProducts();
        $contact = (new CreateContact($repository))->execute(new Actor(1), 'Northwind');
        $repository->saveContact(new Contact(
            $contact->id,
            'Northwind',
            'northwind@example.com',
            'Northwind',
            'qualified',
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
            'Northwind',
            null,
            'Northwind',
            'qualified',
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

    public function test_a_later_send_leaves_the_column_estimate_sent(): void
    {
        $repository = $this->repositoryWithProducts();
        $contact = (new CreateContact($repository))->execute(new Actor(1), 'Northwind');
        $repository->saveContact(new Contact(
            $contact->id,
            'Northwind',
            'northwind@example.com',
            'Northwind',
            'qualified',
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

    private function repositoryWithProducts(): InMemoryCrmRepository
    {
        $repository = new InMemoryCrmRepository;
        $repository->addProduct(new Product(1, 'Cable', 10));
        $repository->addProduct(new Product(2, 'Lamp', 5));

        return $repository;
    }
}
