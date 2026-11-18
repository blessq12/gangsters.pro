<?php

namespace App\Infrastructure\Catalog\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'kind',
        'is_system',
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
        'weight' => 'integer',
        'nutrition' => 'array',
        'ingredients' => 'array',
        'archived_at' => 'datetime',
    ];

    public function prices(): HasMany
    {
        return $this->hasMany(CAT_Price::class, 'item_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(CAT_ItemImage::class, 'item_id');
    }

    public function setLines(): HasMany
    {
        return $this->hasMany(CAT_SetLine::class, 'set_item_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            CAT_Category::class,
            'CAT_category_items',
            'item_id',
            'category_id',
        )->withPivot('sort_order');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            CAT_Tag::class,
            'CAT_item_tags',
            'item_id',
            'tag_id',
        );
    }
}
