<?php

namespace App\Infrastructure\Catalog\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PRD_Product extends Model
{
    protected $table = 'PRD_products';

    protected $fillable = [
        'name',
        'slug',
        'sku',
        'description',
        'status',
        'catalog_kind',
        'price',
        'calories',
        'proteins',
        'fats',
        'carbs',
        'nutrition_basis',
        'ingredients',
        'meta_counts_as_roll',
        'meta_gift_candidate',
        'meta_is_complement_set',
        'archived_at',
    ];

    protected $casts = [
        'price' => 'integer',
        'calories' => 'float',
        'proteins' => 'float',
        'fats' => 'float',
        'carbs' => 'float',
        'meta_counts_as_roll' => 'boolean',
        'meta_gift_candidate' => 'boolean',
        'meta_is_complement_set' => 'boolean',
        'ingredients' => 'array',
        'archived_at' => 'datetime',
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            PRD_Category::class,
            'PRD_category_product',
            'product_id',
            'category_id',
        )->withPivot('sort_order');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            PRD_Tag::class,
            'PRD_product_tag',
            'product_id',
            'tag_id',
        );
    }

    public function setLines(): HasMany
    {
        return $this->hasMany(PRD_ProductSetLine::class, 'set_id')->orderBy('sort_order');
    }

    public function productImages(): HasMany
    {
        return $this->hasMany(PRD_ProductImage::class, 'product_id')->orderBy('sort_order');
    }
}
