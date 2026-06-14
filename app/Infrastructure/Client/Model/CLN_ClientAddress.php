<?php

namespace App\Infrastructure\Client\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CLN_ClientAddress extends Model
{
    protected $table = 'CLN_client_addresses';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'client_id',
        'type',
        'title',
        'street',
        'house',
        'entrance',
        'apartment',
        'comment',
        'is_default',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(CLN_Client::class, 'client_id');
    }
}
