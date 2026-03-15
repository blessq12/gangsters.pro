<?php

namespace App\Infrastructure\Order\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ORD_OrderItem extends Model
{
    use HasFactory;

    protected $table = 'ORD_order_items';

    protected $fillable = [
        'order_id',
        'product_original_id',
        'product_name',
        'product_sku',
        'product_list_price',
        'product_final_price',
        'product_attributes',
        'product_media',
        'quantity',
        'unit_price',
        'row_subtotal',
        'row_discount',
        'row_total',
    ];

    protected $casts = [
        'product_attributes' => 'array',
        'product_media' => 'array',
        'quantity' => 'int',
        'unit_price' => 'int',
        'row_subtotal' => 'int',
        'row_discount' => 'int',
        'row_total' => 'int',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(ORD_Order::class, 'order_id', 'id');
    }
}

