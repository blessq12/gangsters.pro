<?php

namespace App\Infrastructure\Warehouse\Model;

use Illuminate\Database\Eloquent\Model;

final class WH_Ingredient extends Model
{
    protected $table = 'WH_ingredients';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'sku',
        'unit',
    ];
}
