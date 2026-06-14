<?php

namespace App\Infrastructure\Catalog\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PRD_ProductImage extends Model
{
    protected $table = 'PRD_product_images';

    protected $fillable = [
        'product_id',
        'disk',
        'path',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $image): void {
            if (! $image->product_id) {
                return;
            }

            $maxSortOrder = self::query()
                ->where('product_id', $image->product_id)
                ->max('sort_order');

            $image->sort_order = is_null($maxSortOrder) ? 0 : $maxSortOrder + 1;
        });

        static::updating(function (self $image): void {
            if (! $image->isDirty('path')) {
                return;
            }

            $oldPath = $image->getOriginal('path');

            if (! is_string($oldPath) || $oldPath === '' || $oldPath === $image->path) {
                return;
            }

            Storage::disk($image->getOriginal('disk') ?: 'public')->delete($oldPath);
        });

        static::deleting(function (self $image): void {
            if (! is_string($image->path) || $image->path === '') {
                return;
            }

            Storage::disk($image->disk ?: 'public')->delete($image->path);
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(PRD_Product::class, 'product_id');
    }
}
