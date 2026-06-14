<?php

namespace App\Infrastructure\Catalog\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PRD_Category extends Model
{
    protected $table = 'PRD_categories';

    protected $fillable = [
        'name',
        'slug',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $category): void {
            if ($category->sort_order !== null) {
                return;
            }

            $maxSortOrder = self::query()->max('sort_order');

            $category->sort_order = is_null($maxSortOrder) ? 0 : $maxSortOrder + 1;
        });
    }

    public function categoryProducts(): HasMany
    {
        return $this->hasMany(PRD_CategoryProduct::class, 'category_id')->orderBy('sort_order');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            PRD_Product::class,
            'PRD_category_product',
            'category_id',
            'product_id',
        )->withPivot('sort_order');
    }
}
