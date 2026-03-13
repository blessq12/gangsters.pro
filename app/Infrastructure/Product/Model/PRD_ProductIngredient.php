<?php

namespace App\Infrastructure\Product\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PRD_ProductIngredient extends Model
{
    use HasFactory;

    protected $table = 'PRD_product_ingredients';

    protected $fillable = [
        'product_id',
        'name',
        'amount',
        'unit',
        'is_allergen',
    ];

    protected $casts = [
        'is_allergen' => 'bool',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(PRD_Product::class, 'product_id');
    }
}

