<?php

namespace App\Infrastructure\Persistence\Crm;

use Illuminate\Database\Eloquent\Model;

class ContactRecord extends Model
{
    protected $table = 'contacts';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'company',
        'column',
    ];
}
