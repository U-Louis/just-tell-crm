<?php

namespace App\Infrastructure\Persistence\Crm;

use Illuminate\Database\Eloquent\Model;

class VoiceRunRecord extends Model
{
    protected $table = 'voice_runs';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'session_id',
        'acting_user_id',
        'transcript',
        'status',
        'result',
        'applied',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'applied' => 'boolean',
        ];
    }
}
