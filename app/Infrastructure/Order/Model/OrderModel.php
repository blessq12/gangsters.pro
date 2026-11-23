<?php

namespace App\Infrastructure\Order\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class OrderModel extends Model
{
    protected $table = 'ORD_orders';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'source',
        'checkout_id',
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
        'persons',
        'scheduled_at',
        'branch_id',
        'payment_id',
        'payment_method',
        'change_from_rubles',
        'total_kopecks',
        'created_at',
        'updated_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'client_id' => 'integer',
        'delivery_fee_kopecks' => 'integer',
        'delivery_lat' => 'float',
        'delivery_lng' => 'float',
        'persons' => 'integer',
        'scheduled_at' => 'datetime',
        'branch_id' => 'integer',
        'payment_id' => 'integer',
        'change_from_rubles' => 'integer',
        'total_kopecks' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(OrderLineModel::class, 'order_id')
            ->orderBy('position')
            ->orderBy('id');
    }
}
