<?php

namespace App\Infrastructure\Order\Model;

use Illuminate\Database\Eloquent\Model;

final class ORD_Order extends Model
{
    public $timestamps = false;

    protected $table = 'ORD_orders';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'source',
        'checkout_id',
        'partner_code',
        'external_order_id',
        'status',
        'cart_snapshot',
        'client_snapshot',
        'delivery_snapshot',
        'payment_snapshot',
        'client_id',
        'total_kopecks',
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
        'client_id' => 'integer',
        'total_kopecks' => 'integer',
        'created_at' => 'datetime',
    ];
}
