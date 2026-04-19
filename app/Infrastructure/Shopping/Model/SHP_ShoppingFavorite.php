<?php

namespace App\Infrastructure\Shopping\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SHP_ShoppingFavorite extends Model
{
    protected $table = 'SHP_shopping_favorites';

    protected $fillable = [
        'shopping_session_id',
        'product_id',
    ];

    protected $casts = [
        'product_id' => 'integer',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(SHP_ShoppingSession::class, 'shopping_session_id');
    }
}
