<?php

namespace App\Infrastructure\Order\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $order_id
 * @property int $product_id
 * @property string $name
 * @property int $unit_price_kopecks
 * @property int $quantity
 * @property string $line_type
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
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
