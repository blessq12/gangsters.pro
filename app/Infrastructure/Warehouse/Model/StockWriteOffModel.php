<?php

namespace App\Infrastructure\Warehouse\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $branch_id
 * @property int $ingredient_id
 * @property mixed $quantity
 * @property string $reason
 * @property int $kitchen_ticket_id
 * @property Carbon|null $occurred_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class StockWriteOffModel extends Model
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
