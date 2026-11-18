<?php

namespace App\Domain\Crm;

final class CreateProduct
{
    public function __construct(private CrmRepository $crm) {}

    public function execute(Actor $actor, string $name, int $unitPrice): Product
    {
        if ($actor->id < 1) {
            throw new CrmException('user.required', 'F16');
        }

        $name = trim($name);
        if ($name === '') {
            throw new CrmException('product.name.required', 'F17', [
                'name' => 'product.name.required',
            ]);
        }

        if ($unitPrice < 0) {
            throw new CrmException('product.unit_price.invalid', 'F19', [
                'unit_price' => 'product.unit_price.invalid',
            ]);
        }

        if ($this->crm->productNameTaken($name)) {
            throw new CrmException('product.name.taken', 'F18', [
                'name' => 'product.name.taken',
            ]);
        }

        return $this->crm->createProduct($name, $unitPrice);
    }
}
