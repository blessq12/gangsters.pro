<?php

namespace App\Infrastructure\MarketingContent\Model;

use App\Infrastructure\MarketingContent\Support\MarketingStoredMedia;
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

        static::updating(function (self $banner): void {
            foreach (['image_desktop', 'image_mobile'] as $field) {
                if (! $banner->isDirty($field)) {
                    continue;
                }

                $oldPath = $banner->getOriginal($field);

                if (! is_string($oldPath) || $oldPath === '' || $oldPath === $banner->{$field}) {
                    continue;
                }

                MarketingStoredMedia::deleteIfStored($oldPath);
            }
        });

        static::deleting(function (self $banner): void {
            MarketingStoredMedia::deleteIfStored($banner->image_desktop);
            MarketingStoredMedia::deleteIfStored($banner->image_mobile);
        });
    }
}
