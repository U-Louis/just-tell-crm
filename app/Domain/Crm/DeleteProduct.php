<?php

namespace App\Domain\Crm;

final class DeleteProduct
{
    public function __construct(private CrmRepository $crm) {}

    public function execute(Actor $actor, int $productId): void
    {
        if ($actor->id < 1) {
            throw new CrmException('user.required', 'F37');
        }

        $product = $this->crm->findProduct($productId);
        if ($product === null) {
            throw new \RuntimeException('Product is missing.');
        }

        if ($this->crm->productOnEstimateLine($productId)) {
            throw new CrmException('product.delete.blocked', 'F21');
        }

        $this->crm->deleteProduct($productId);
    }
}
