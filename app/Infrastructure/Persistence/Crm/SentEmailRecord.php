<?php

namespace App\Infrastructure\Persistence\Crm;

use Illuminate\Database\Eloquent\Model;

class SentEmailRecord extends Model
{
    protected $table = 'sent_emails';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'contact_id',
        'to_address',
        'body',
    ];
}
