<?php

namespace App\Infrastructure\Catalog\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PRD_Tag extends Model
{
    protected $table = 'PRD_tags';

    protected $fillable = [
        'code',
        'label',
        'color',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $tag): void {
            if ($tag->sort_order !== null) {
                return;
            }

            $maxSortOrder = self::query()->max('sort_order');

            $tag->sort_order = is_null($maxSortOrder) ? 0 : $maxSortOrder + 1;
        });
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            PRD_Product::class,
            'PRD_product_tag',
            'tag_id',
            'product_id',
        );
    }
}
