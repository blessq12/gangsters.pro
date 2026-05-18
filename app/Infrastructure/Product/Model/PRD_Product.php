<?php

namespace App\Infrastructure\Product\Model;

use App\Domain\Product\Entity\Product as ProductEntity;
use App\Infrastructure\Category\Model\PRD_Category;
use App\Infrastructure\Category\Model\PRD_CategoryProduct;
use App\Support\Slug\UniqueSlugGenerator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PRD_Product extends Model
{
    use HasFactory;

    protected $table = 'PRD_products';

    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            if ($model->isDirty('name') || $model->slug === null || $model->slug === '') {
                $model->slug = app(UniqueSlugGenerator::class)->uniqueFrom(
                    $model->name,
                    self::class,
                    $model->id
                );
            }

            if ($model->isDirty('status')) {
                if ($model->status === ProductEntity::STATUS_ARCHIVED) {
                    if ($model->archived_at === null) {
                        $model->archived_at = now();
                    }
                } elseif ($model->status === ProductEntity::STATUS_ACTIVE) {
                    $model->archived_at = null;
                }
            }
        });
    }

    protected $fillable = [
        'name',
        'articul',
        'description',
        'price',
        'status',
        'cart_rule_counts_as_roll',
        'cart_rule_gift_candidate',
        'cart_rule_is_complement_set',
        'calories',
        'proteins',
        'fats',
        'carbs',
        'nutrition_basis',
    ];

    protected $casts = [
        'calories' => 'float',
        'proteins' => 'float',
        'fats' => 'float',
        'carbs' => 'float',
        'price' => 'integer',
        'cart_rule_counts_as_roll' => 'boolean',
        'cart_rule_gift_candidate' => 'boolean',
        'cart_rule_is_complement_set' => 'boolean',
    ];

    public function images(): HasMany
    {
        return $this->hasMany(PRD_ProductImage::class, 'product_id')
            ->orderBy('sort_order');
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(PRD_ProductIngredient::class, 'product_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            PRD_Tag::class,
            'PRD_product_tag',
            'product_id',
            'tag_id',
        )->withTimestamps()->orderBy('sort_order');
    }

    public function categoryLinks(): HasMany
    {
        return $this->hasMany(PRD_CategoryProduct::class, 'product_id')
            ->orderBy('sort_order');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            PRD_Category::class,
            'PRD_category_product',
            'product_id',
            'category_id',
        )
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }
}
