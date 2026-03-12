<?php

namespace App\Infrastructure\Client\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UR_ClientAddress extends Model
{
    use HasFactory;

    protected $table = 'UR_client_addresses';

    protected $fillable = [
        'client_id',
        'type',
        'title',
        'street',
        'house',
        'liter',
        'staircase',
        'apartment',
        'entrance_code',
        'floor',
        'comment',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(UR_Client::class, 'client_id');
    }
}

