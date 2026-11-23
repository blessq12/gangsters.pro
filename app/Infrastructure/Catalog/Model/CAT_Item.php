<?php

namespace App\Infrastructure\Catalog\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class CAT_Item extends Model
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
        return $this->hasMany(CAT_ItemImage::class, 'item_id');
    }
}
