<?php

namespace App\Infrastructure\Warehouse\Model;

use Illuminate\Database\Eloquent\Model;

final class WH_StockWriteOff extends Model
{
    protected $table = 'WH_stock_writeoffs';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'ingredient_id',
        'quantity',
        'reason',
        'kitchen_ticket_id',
        'occurred_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'ingredient_id' => 'integer',
        'quantity' => 'float',
        'kitchen_ticket_id' => 'integer',
        'occurred_at' => 'datetime',
    ];
}
