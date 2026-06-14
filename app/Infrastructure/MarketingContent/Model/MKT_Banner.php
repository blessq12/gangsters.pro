<?php

namespace App\Infrastructure\MarketingContent\Model;

use Illuminate\Database\Eloquent\Model;

class MKT_Banner extends Model
{
    protected $table = 'MKT_banners';

    protected $fillable = [
        'title',
        'description',
        'image_desktop',
        'image_mobile',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $banner): void {
            if ($banner->sort_order !== null) {
                return;
            }

            $maxSortOrder = self::query()->max('sort_order');
            $banner->sort_order = is_null($maxSortOrder) ? 0 : $maxSortOrder + 1;
        });
    }
}
