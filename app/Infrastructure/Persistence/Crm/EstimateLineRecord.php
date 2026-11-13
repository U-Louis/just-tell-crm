<?php

namespace App\Infrastructure\Persistence\Crm;

use Illuminate\Database\Eloquent\Model;

class EstimateLineRecord extends Model
{
    protected $table = 'estimate_lines';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'estimate_id',
        'product_id',
        'quantity',
        'unit_price',
    ];
}
