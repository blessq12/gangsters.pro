<?php

namespace App\Infrastructure\Warehouse\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class WH_Ingredient extends Model
{
    protected $table = 'WH_ingredients';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'sku',
        'unit',
    ];

    public function stockLevel(): HasOne
    {
        return $this->hasOne(WH_StockLevel::class, 'ingredient_id');
    }
}
