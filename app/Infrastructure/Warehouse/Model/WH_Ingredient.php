<?php

namespace App\Infrastructure\Warehouse\Model;

use Illuminate\Database\Eloquent\Model;

final class WH_Ingredient extends Model
{
    protected $table = 'WH_ingredients';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'unit',
    ];
}
