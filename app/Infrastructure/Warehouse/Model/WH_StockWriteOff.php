<?php

namespace App\Infrastructure\Warehouse\Model;

use Illuminate\Database\Eloquent\Model;

final class WH_StockWriteOff extends Model
{
    protected $table = 'WH_stock_writeoffs';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'branch_id',
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
        'branch_id' => 'integer',
        'ingredient_id' => 'integer',
        'quantity' => 'decimal:3',
        'kitchen_ticket_id' => 'integer',
        'occurred_at' => 'datetime',
    ];
}
