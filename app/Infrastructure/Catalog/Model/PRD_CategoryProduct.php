<?php

namespace App\Infrastructure\Catalog\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PRD_CategoryProduct extends Model
{
    protected $table = 'PRD_category_product';

    protected $fillable = [
        'category_id',
        'product_id',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $line): void {
            if (! $line->category_id) {
                return;
            }

            $maxSortOrder = self::query()
                ->where('category_id', $line->category_id)
                ->max('sort_order');

            $line->sort_order = is_null($maxSortOrder) ? 0 : $maxSortOrder + 1;
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(PRD_Product::class, 'product_id');
    }
}
