<?php

namespace App\Infrastructure\Product\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PRD_ProductPrice extends Model
{
    use HasFactory;

    protected $table = 'PRD_product_prices';

    protected $fillable = [
        'product_id',
        'amount',
        'customer_status',
        'is_default',
    ];

    protected $casts = [
        'amount' => 'integer',
        'is_default' => 'bool',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(PRD_Product::class, 'product_id');
    }
}

