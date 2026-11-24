<?php

namespace App\Domain\Crm;

final class UpdateProduct
{
    public function __construct(private CrmRepository $crm) {}

    public function execute(Actor $actor, int $productId, string $name, int $unitPrice, ?string $description = null, ?string $category = null): Product
    {
        if ($actor->id < 1) {
            throw new CrmException('user.required', 'F20');
        }

        $product = $this->crm->findProduct($productId);
        if ($product === null) {
            throw new \RuntimeException('Product is missing.');
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

        if ($this->crm->productNameTaken($name, $productId)) {
            throw new CrmException('product.name.taken', 'F18', [
                'name' => 'product.name.taken',
            ]);
        }

        return $this->crm->saveProduct(new Product(
            $productId,
            $name,
            $unitPrice,
            FieldText::optional($description, 255, 'product.description.invalid', 'F20', 'description'),
            ProductCategory::optional($category, 'F20'),
        ));
    }
}
