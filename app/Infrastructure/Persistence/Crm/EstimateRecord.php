<?php

namespace App\Infrastructure\Persistence\Crm;

use Illuminate\Database\Eloquent\Model;

class EstimateRecord extends Model
{
    protected $table = 'estimates';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'contact_id',
        'grand_total',
    ];
}
