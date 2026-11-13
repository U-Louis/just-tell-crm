<?php

namespace App\Domain\Crm;

final class SendEstimate
{
    public const BODY = 'This is a test.';

    public function __construct(private CrmRepository $crm) {}

    /**
     * @param  list<array{product_id: int, quantity: int}>  $lines
     */
    public function execute(Actor $actor, int $contactId, array $lines): Contact
    {
        if ($actor->id < 1) {
            throw new CrmException('user.required', 'F4');
        }

        $contact = $this->crm->findContact($contactId);
        if ($contact === null) {
            throw new \RuntimeException('Contact is missing.');
        }

        if ($contact->email === null || trim($contact->email) === '') {
            throw new CrmException('contact.email.required', 'F5', [
                'email' => 'contact.email.required',
            ]);
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
            throw new \RuntimeException('Estimate lines are missing.');
        }

        $sending = new Contact(
            $contact->id,
            $contact->userId,
            $contact->name,
            $contact->email,
            $contact->company,
            Column::ESTIMATE_SENT,
            self::BODY,
            $total,
        );

        return $this->crm->recordEstimateSend(
            $sending,
            $priced,
            $total,
            $contact->email,
            self::BODY,
        );
    }
}
