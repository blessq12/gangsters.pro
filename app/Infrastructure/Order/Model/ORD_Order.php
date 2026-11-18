<?php

namespace App\Infrastructure\Order\Model;

use Illuminate\Database\Eloquent\Model;

final class ORD_Order extends Model
{
    public $timestamps = false;

    protected $table = 'ORD_orders';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'source',
        'checkout_id',
        'partner_code',
        'external_order_id',
        'status',
        'client_id',
        'total_rubles',
        'cart_snapshot',
        'client_snapshot',
        'delivery_snapshot',
        'payment_snapshot',
        'created_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'cart_snapshot' => 'array',
        'client_snapshot' => 'array',
        'delivery_snapshot' => 'array',
        'payment_snapshot' => 'array',
        'created_at' => 'datetime',
    ];
}
