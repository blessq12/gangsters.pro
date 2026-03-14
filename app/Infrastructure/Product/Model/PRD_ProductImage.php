<?php

namespace App\Infrastructure\Product\Model;

use App\Services\Catalog\ProductImageVariantService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PRD_ProductImage extends Model
{
    use HasFactory;

    protected $table = 'PRD_product_images';

    protected $fillable = [
        'product_id',
        'sort_order',
        'thumb_path',
        'thumb_width',
        'thumb_height',
        'medium_path',
        'medium_width',
        'medium_height',
        'large_path',
        'large_width',
        'large_height',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $image): void {
            if (! $image->thumb_path) {
                return;
            }

            $needVariants = ! $image->medium_path
                || $image->medium_path === $image->thumb_path
                || $image->large_path === $image->thumb_path;

            if ($needVariants) {
                $service = app(ProductImageVariantService::class);
                $result = $service->generateVariants($image->thumb_path);

                if ($result !== null) {
                    $image->thumb_path = $result['thumb']['path'];
                    $image->thumb_width = $result['thumb']['width'];
                    $image->thumb_height = $result['thumb']['height'];
                    $image->medium_path = $result['medium']['path'];
                    $image->medium_width = $result['medium']['width'];
                    $image->medium_height = $result['medium']['height'];
                    $image->large_path = $result['large']['path'];
                    $image->large_width = $result['large']['width'];
                    $image->large_height = $result['large']['height'];

                    return;
                }
            }

            if (! $image->medium_path) {
                $image->medium_path = $image->thumb_path;
            }
            if (! $image->large_path) {
                $image->large_path = $image->thumb_path;
            }

            try {
                $relativePath = ltrim($image->thumb_path, '/');
                $fullPath = storage_path('app/public/'.$relativePath);

                if (is_file($fullPath)) {
                    $size = @getimagesize($fullPath);
                    if (is_array($size) && isset($size[0], $size[1])) {
                        [$width, $height] = $size;
                        if (! $image->thumb_width || ! $image->thumb_height) {
                            $image->thumb_width = $width;
                            $image->thumb_height = $height;
                        }
                        if (! $image->medium_width || ! $image->medium_height) {
                            $image->medium_width = $width;
                            $image->medium_height = $height;
                        }
                        if (! $image->large_width || ! $image->large_height) {
                            $image->large_width = $width;
                            $image->large_height = $height;
                        }
                    }
                }
            } catch (\Throwable) {
                // fallback: игнорируем
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(PRD_Product::class, 'product_id');
    }
}

