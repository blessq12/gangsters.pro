<?php

namespace App\Infrastructure\Catalog\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $sku
 * @property string $description
 * @property string $status
 * @property bool $is_system
 * @property bool $is_roll
 * @property int $weight
 * @property array $nutrition
 * @property array $ingredients
 * @property Carbon|null $archived_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class CatalogItemModel extends Model
{
    protected $table = 'CAT_items';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'sku',
        'description',
        'status',
        'is_system',
        'is_roll',
        'weight',
        'nutrition',
        'ingredients',
        'archived_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_system' => 'boolean',
        'is_roll' => 'boolean',
        'weight' => 'integer',
        'nutrition' => 'array',
        'ingredients' => 'array',
        'archived_at' => 'datetime',
    ];

    public function images(): HasMany
    {
        return $this->hasMany(CatalogItemImageModel::class, 'item_id');
    }
}
