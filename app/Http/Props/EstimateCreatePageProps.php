<?php

namespace App\Http\Props;

use App\Domain\Crm\Contact;
use App\Domain\Crm\Product;

final class EstimateCreatePageProps
{
    /**
     * @param  list<Contact>  $contacts
     * @param  list<Product>  $products
     * @return array<string, mixed>
     */
    public static function make(array $contacts, array $products): array
    {
        return [
            'contacts' => array_map(fn (Contact $contact) => [
                'id' => $contact->id,
                'name' => $contact->name,
                'storeUrl' => route('contacts.estimates.store', $contact->id),
            ], $contacts),
            'products' => array_map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'unitPrice' => $product->unitPrice,
            ], $products),
        ];
    }
}
