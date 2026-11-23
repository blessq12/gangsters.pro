<?php

namespace App\Infrastructure\Order\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class OrderLineModel extends Model
{
    public $timestamps = false;

    protected $table = 'ORD_order_lines';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'name',
        'unit_price_kopecks',
        'quantity',
        'line_type',
        'position',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'order_id' => 'integer',
        'product_id' => 'integer',
        'unit_price_kopecks' => 'integer',
        'quantity' => 'integer',
        'position' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderModel::class, 'order_id');
    }
}
