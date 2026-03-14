<?php

namespace App\Infrastructure\Category\Model;

use App\Services\Slug\TransliteratingSlugGenerator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PRD_Category extends Model
{
    use HasFactory;

    protected $table = 'PRD_categories';

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
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'bool',
    ];

    public function links(): HasMany
    {
        return $this->hasMany(PRD_CategoryProduct::class, 'category_id');
    }
}

