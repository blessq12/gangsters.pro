<?php

namespace App\Infrastructure\Warehouse\Model;

use Illuminate\Database\Eloquent\Model;

final class WH_StockLevel extends Model
{
    protected $table = 'WH_stock_levels';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'ingredient_id',
        'quantity',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'ingredient_id' => 'integer',
        'quantity' => 'float',
    ];
}
