<?php

namespace Tests\Unit\Domain;

use App\Domain\Crm\Actor;
use App\Domain\Crm\CompanySize;
use App\Domain\Crm\CreateContact;
use App\Domain\Crm\CreateProduct;
use App\Domain\Crm\Fixtures\ContactFactory;
use App\Domain\Crm\Fixtures\FixtureService;
use App\Domain\Crm\ProductCategory;
use PHPUnit\Framework\TestCase;

class FixtureServiceTest extends TestCase
{
    public function test_import_contact_fills_fixture_fields_and_stays_new(): void
    {
        $repository = new InMemoryCrmRepository;
        $service = $this->service($repository);

        $contact = $service->importContact(new Actor(1));

        $this->assertSame(ContactFactory::COLUMN, $contact->column);
        $this->assertSame('new', $contact->column);
        $this->assertNotSame('', $contact->name);
        $this->assertNotNull($contact->email);
        $this->assertNotNull($contact->company);
        $this->assertNotNull($contact->description);
        $this->assertNotNull($contact->notes);
        $this->assertContains($contact->companySize, CompanySize::VALUES);
        $this->assertSame(1, $contact->ownerId);
        $this->assertSame(1, $contact->userId);
    }

    public function test_import_product_fills_price_description_and_category(): void
    {
        $repository = new InMemoryCrmRepository;
        $product = $this->service($repository)->importProduct(new Actor(1));

        $this->assertNotSame('', $product->name);
        $this->assertGreaterThan(0, $product->unitPrice);
        $this->assertNotNull($product->description);
        $this->assertContains($product->category, ProductCategory::VALUES);
    }

    public function test_import_estimate_creates_a_contact_and_one_to_three_lines_when_none_exist(): void
    {
        $repository = new InMemoryCrmRepository;
        $estimate = $this->service($repository)->importEstimate(new Actor(1));

        $this->assertCount(1, $repository->listContacts(1));
        $this->assertGreaterThanOrEqual(1, count($estimate->lines));
        $this->assertLessThanOrEqual(3, count($estimate->lines));
        $this->assertNotNull($estimate->sentAt);
        $this->assertSame('new', $repository->findContact($estimate->contactId)?->column);
    }

    public function test_import_estimate_reuses_an_existing_contact(): void
    {
        $repository = new InMemoryCrmRepository;
        $service = $this->service($repository);
        $service->importContact(new Actor(1));

        $estimate = $service->importEstimate(new Actor(1));

        $this->assertCount(1, $repository->listContacts(1));
        $this->assertSame(1, $estimate->contactId);
        $this->assertGreaterThanOrEqual(1, count($estimate->lines));
        $this->assertLessThanOrEqual(3, count($estimate->lines));
    }

    private function service(InMemoryCrmRepository $repository): FixtureService
    {
        return new FixtureService($repository, new CreateContact($repository), new CreateProduct($repository));
    }
}
