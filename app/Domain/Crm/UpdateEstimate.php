<?php

namespace App\Domain\Crm;

final class UpdateEstimate
{
    public function __construct(private CrmRepository $crm) {}

    /**
     * @param  list<array{product_id: int, quantity: int}>  $lines
     */
    public function execute(Actor $actor, int $estimateId, array $lines): void
    {
        if ($actor->id < 1) {
            throw new CrmException('user.required', 'F22');
        }

        $estimate = $this->crm->findEstimate($estimateId);
        if ($estimate === null) {
            throw new \RuntimeException('Estimate is missing.');
        }

        $priced = [];
        $total = 0;
        foreach ($lines as $line) {
            $quantity = (int) $line['quantity'];
            if ($quantity < 1) {
                continue;
            }

            $product = $this->crm->findProduct((int) $line['product_id']);
            if ($product === null) {
                throw new \RuntimeException('Product is missing.');
            }

            $total += $quantity * $product->unitPrice;
            $priced[] = new EstimateLine($product->id, $quantity, $product->unitPrice);
        }

        if ($priced === []) {
            throw new CrmException('estimate.lines.required', 'F23', [
                'lines' => 'estimate.lines.required',
            ]);
        }

        $this->crm->replaceEstimateLines($estimateId, $priced, $total);
    }
}
