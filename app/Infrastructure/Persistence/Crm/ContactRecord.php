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
        'user_id',
        'name',
        'email',
        'company',
        'column',
        'description',
        'notes',
        'company_size',
        'owner_id',
        'validated',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'validated' => 'boolean',
        ];
    }
}
