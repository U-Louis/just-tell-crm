<?php

namespace App\Infrastructure\Persistence\Crm;

use Illuminate\Database\Eloquent\Model;

class ProductRecord extends Model
{
    protected $table = 'products';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'unit_price',
    ];
}
