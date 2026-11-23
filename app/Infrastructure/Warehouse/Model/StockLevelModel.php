<?php

namespace App\Infrastructure\Warehouse\Model;

use Illuminate\Database\Eloquent\Model;

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
