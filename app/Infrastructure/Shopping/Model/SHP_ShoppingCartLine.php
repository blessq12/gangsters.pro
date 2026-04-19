<?php

namespace App\Infrastructure\Shopping\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SHP_ShoppingCartLine extends Model
{
    protected $table = 'SHP_shopping_cart_lines';

    protected $fillable = [
        'shopping_session_id',
        'product_id',
        'quantity',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
        'quantity' => 'integer',
        'product_id' => 'integer',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(SHP_ShoppingSession::class, 'shopping_session_id');
    }
}
