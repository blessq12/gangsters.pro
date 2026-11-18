<?php

namespace App\Infrastructure\Crm\Model;

use Illuminate\Database\Eloquent\Model;

final class CRM_ClientAddress extends Model
{
    protected $table = 'CRM_client_addresses';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'client_id',
        'address_key',
        'type',
        'title',
        'street',
        'house',
        'apartment',
        'entrance',
        'floor',
        'comment',
        'is_default',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'client_id' => 'integer',
        'is_default' => 'boolean',
    ];
}
