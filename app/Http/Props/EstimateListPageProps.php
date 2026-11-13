<?php

namespace App\Http\Props;

use App\Domain\Crm\ListedEstimate;

final class EstimateListPageProps
{
    /**
     * @param  list<ListedEstimate>  $estimates
     * @return array<string, mixed>
     */
    public static function make(array $estimates): array
    {
        return [
            'estimates' => array_map(fn (ListedEstimate $estimate) => [
                'id' => $estimate->id,
                'contactName' => $estimate->contactName,
                'grandTotal' => $estimate->grandTotal,
            ], $estimates),
        ];
    }
}
