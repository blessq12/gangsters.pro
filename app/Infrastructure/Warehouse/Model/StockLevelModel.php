<?php

namespace App\Infrastructure\Warehouse\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $branch_id
 * @property int $ingredient_id
 * @property mixed $quantity
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class StockLevelModel extends Model
{
    protected $table = 'WH_stock_levels';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'branch_id',
        'ingredient_id',
        'quantity',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'branch_id' => 'integer',
        'ingredient_id' => 'integer',
        'quantity' => 'decimal:3',
    ];
}
