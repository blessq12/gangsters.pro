<?php

namespace App\Infrastructure\MarketingContent\Model;

use Illuminate\Database\Eloquent\Model;

class MKT_Promotion extends Model
{
    protected $table = 'MKT_promotions';

    protected $fillable = [
        'title',
        'body',
        'image',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $promotion): void {
            if ($promotion->sort_order !== null) {
                return;
            }

            $maxSortOrder = self::query()->max('sort_order');
            $promotion->sort_order = is_null($maxSortOrder) ? 0 : $maxSortOrder + 1;
        });
    }
}
