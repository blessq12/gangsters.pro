<?php

namespace App\Infrastructure\Product\Model;

use App\Services\Slug\TransliteratingSlugGenerator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PRD_Product extends Model
{
    use HasFactory;

    protected $table = 'PRD_products';

    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            if ($model->isDirty('name') || $model->slug === null || $model->slug === '') {
                $model->slug = app(TransliteratingSlugGenerator::class)->uniqueFrom(
                    $model->name,
                    self::class,
                    $model->id
                );
            }
        });
    }

    protected $fillable = [
        'name',
        'articul',
        'description',
        'status',
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

    public function tags(): HasMany
    {
        return $this->hasMany(PRD_ProductTag::class, 'product_id');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(PRD_ProductPrice::class, 'product_id');
    }
}

