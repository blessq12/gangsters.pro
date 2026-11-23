<?php

namespace App\Infrastructure\Client\Model;

use Illuminate\Database\Eloquent\Model;

final class ClientAddressModel extends Model
{
    protected $table = 'CLI_client_addresses';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'client_id',
        'street',
        'house',
        'apartment',
        'entrance',
        'floor',
        'comment',
        'lat',
        'lng',
        'is_default',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'client_id' => 'integer',
        'lat' => 'float',
        'lng' => 'float',
        'is_default' => 'boolean',
    ];
}
