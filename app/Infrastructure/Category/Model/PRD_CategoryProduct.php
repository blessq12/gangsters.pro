<?php

namespace App\Infrastructure\Category\Model;

use App\Infrastructure\Product\Model\PRD_Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PRD_CategoryProduct extends Model
{
    use HasFactory;

    protected $table = 'PRD_category_product';

    protected $fillable = [
        'category_id',
        'product_id',
        'sort_order',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(PRD_Category::class, 'category_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(PRD_Product::class, 'product_id');
    }
}

