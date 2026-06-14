<?php

namespace App\Infrastructure\Client\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CLN_ClientFavorite extends Model
{
    protected $table = 'CLN_client_favorites';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'client_id',
        'product_id',
        'product_name',
        'price_rub',
        'weight',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'product_id' => 'integer',
        'price_rub' => 'float',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(CLN_Client::class, 'client_id');
    }
}
