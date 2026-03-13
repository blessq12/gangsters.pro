<?php

namespace App\Infrastructure\Product\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PRD_ProductTag extends Model
{
    use HasFactory;

    protected $table = 'PRD_product_tags';

    protected $fillable = [
        'product_id',
        'code',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(PRD_Product::class, 'product_id');
    }
}

