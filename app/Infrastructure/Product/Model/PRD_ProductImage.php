<?php

namespace App\Infrastructure\Product\Model;

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

    public function product(): BelongsTo
    {
        return $this->belongsTo(PRD_Product::class, 'product_id');
    }
}

