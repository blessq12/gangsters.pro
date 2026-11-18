<?php

namespace App\Infrastructure\Crm\Model;

use Illuminate\Database\Eloquent\Model;

final class CRM_ClientFavorite extends Model
{
    protected $table = 'CRM_client_favorites';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'client_id',
        'catalog_item_id',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'client_id' => 'integer',
        'catalog_item_id' => 'integer',
    ];
}
