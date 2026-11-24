<?php

namespace App\Domain\Crm\Fixtures;

use App\Domain\Crm\Actor;
use App\Domain\Crm\Contact;
use App\Domain\Crm\CreateContact;
use App\Domain\Crm\CreateProduct;
use App\Domain\Crm\CrmRepository;
use App\Domain\Crm\EstimateDetail;
use App\Domain\Crm\Product;

final class FixtureService
{
    public function __construct(
        private CrmRepository $crm,
        private CreateContact $createContact,
        private CreateProduct $createProduct,
    ) {}

    public function importContact(Actor $actor): Contact
    {
        $fixture = ContactFactory::make($actor->id);

        return $this->createContact->execute(
            $actor,
            $fixture->name,
            $fixture->email,
            $fixture->company,
            $fixture->description,
            $fixture->notes,
            $fixture->companySize,
            $fixture->ownerId,
        );
    }

    public function importProduct(Actor $actor): Product
    {
        for ($attempt = 0; $attempt < 8; $attempt++) {
            $fixture = ProductFactory::make();
            if ($this->crm->productNameTaken($fixture->name)) {
                continue;
            }

            return $this->createProduct->execute(
                $actor,
                $fixture->name,
                $fixture->unitPrice,
                $fixture->description,
                $fixture->category,
            );
        }

        throw new \RuntimeException('Product fixture name is taken.');
    }

    public function importEstimate(Actor $actor): EstimateDetail
    {
        $contacts = $this->crm->listContacts($actor->id);
        if ($contacts === []) {
            $contacts = [$this->importContact($actor)];
        }

        $contact = $contacts[array_rand($contacts)];
        if ($contact->id === null) {
            throw new \RuntimeException('Contact is missing.');
        }

        $count = random_int(1, 3);
        $products = $this->crm->listProducts();
        while (count($products) < $count) {
            $products[] = $this->importProduct($actor);
        }

        $lines = EstimateFactory::lines($products, $count);

        return $this->crm->addEstimate($contact->id, $lines, EstimateFactory::total($lines));
    }
}
