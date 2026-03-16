<?php

namespace App\Infrastructure\Client\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UR_ClientAddress extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'UR_client_addresses';

    protected $fillable = [
        'client_id',
        'type',
        'title',
        'street',
        'house',
        'entrance',
        'apartment',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(UR_Client::class, 'client_id');
    }
}

