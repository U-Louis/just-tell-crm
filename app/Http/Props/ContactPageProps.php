<?php

namespace App\Http\Props;

use App\Domain\Crm\Contact;
use App\Domain\Crm\Product;

final class ContactPageProps
{
    /**
     * @param  list<Product>  $products
     * @return array<string, mixed>
     */
    public static function make(Contact $contact, array $products): array
    {
        return [
            'contact' => [
                'name' => $contact->name,
                'email' => $contact->email,
                'company' => $contact->company,
                'column' => $contact->column,
                'sentEmailBody' => $contact->sentEmailBody,
                'grandTotal' => $contact->grandTotal,
            ],
            'products' => array_map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'unitPrice' => $product->unitPrice,
            ], $products),
            'urls' => [
                'qualify' => route('contacts.qualify', $contact->id),
                'estimate' => route('contacts.estimates.store', $contact->id),
            ],
        ];
    }
}
