<?php

namespace App\Infrastructure\Order\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ORD_Order extends Model
{
    use HasFactory;

    protected $table = 'ORD_orders';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'client_id',
        'status',
        'subtotal',
        'discount_total',
        'total',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_address',
        'delivery_method',
        'delivery_address',
        'delivery_comment',
        'payment_method',
        'payment_status',
    ];

    protected $casts = [
        'customer_address' => 'array',
        'delivery_address' => 'array',
        'subtotal' => 'int',
        'discount_total' => 'int',
        'total' => 'int',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(ORD_OrderItem::class, 'order_id', 'id');
    }
}

