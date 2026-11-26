<?php

namespace App\Infrastructure\Warehouse\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $unit
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class IngredientModel extends Model
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
