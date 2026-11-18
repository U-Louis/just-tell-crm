<?php

namespace App\Domain\Crm;

final class DeleteEstimate
{
    public function __construct(private CrmRepository $crm) {}

    public function execute(Actor $actor, int $estimateId): void
    {
        if ($actor->id < 1) {
            throw new CrmException('user.required', 'F24');
        }

        $estimate = $this->crm->findEstimate($estimateId);
        if ($estimate === null) {
            throw new \RuntimeException('Estimate is missing.');
        }

        $this->crm->deleteEstimate($estimateId);
    }
}
