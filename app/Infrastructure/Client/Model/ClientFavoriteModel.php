<?php

namespace App\Infrastructure\Client\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $client_id
 * @property int $catalog_item_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class ClientFavoriteModel extends Model
{
    protected $table = 'CLI_client_favorites';

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
