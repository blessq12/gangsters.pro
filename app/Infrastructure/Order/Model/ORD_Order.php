<?php

namespace App\Infrastructure\Order\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'client_id',
        'client_name',
        'client_phone',
        'delivery_type',
        'delivery_zone_id',
        'delivery_fee_kopecks',
        'delivery_street',
        'delivery_house',
        'delivery_entrance',
        'delivery_floor',
        'delivery_apartment',
        'delivery_comment',
        'delivery_lat',
        'delivery_lng',
        'branch_id',
        'payment_id',
        'payment_method',
        'change_from_rubles',
        'total_kopecks',
        'created_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'client_id' => 'integer',
        'delivery_fee_kopecks' => 'integer',
        'delivery_lat' => 'float',
        'delivery_lng' => 'float',
        'branch_id' => 'integer',
        'payment_id' => 'integer',
        'change_from_rubles' => 'integer',
        'total_kopecks' => 'integer',
        'created_at' => 'datetime',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(ORD_OrderLine::class, 'order_id')
            ->orderBy('position')
            ->orderBy('id');
    }
}
