<?php

namespace App\Http\Props;

use App\Domain\Crm\EstimateDetail;

final class EstimatePageProps
{
    /**
     * @return array<string, mixed>
     */
    public static function make(EstimateDetail $estimate): array
    {
        return [
            'estimate' => [
                'id' => $estimate->id,
                'contactName' => $estimate->contactName,
                'grandTotal' => $estimate->grandTotal,
                'sentAt' => $estimate->sentAt,
                'lines' => array_map(fn ($line) => [
                    'productId' => $line->productId,
                    'productName' => $line->productName,
                    'quantity' => $line->quantity,
                    'unitPrice' => $line->unitPrice,
                    'currentUnitPrice' => $line->currentUnitPrice,
                ], $estimate->lines),
            ],
            'urls' => [
                'update' => route('estimates.update', $estimate->id),
                'destroy' => route('estimates.destroy', $estimate->id),
                'pdf' => route('estimates.pdf', $estimate->id),
            ],
        ];
    }
}
