<?php

namespace App\Infrastructure\Promotion\Model;

use Illuminate\Database\Eloquent\Model;

class PRM_Configuration extends Model
{
    protected $table = 'PRM_configuration';

    protected $fillable = [
        'gift_pickup_min_order_kopecks',
        'gift_courier_min_order_kopecks',
        'gift_benefit_active',
        'delivery_free_threshold_kopecks',
        'delivery_outside_zone_surcharge_kopecks',
        'delivery_benefit_active',
        'complement_set_benefit_active',
        'complement_set_rolls_per_set',
    ];

    protected $casts = [
        'gift_pickup_min_order_kopecks' => 'integer',
        'gift_courier_min_order_kopecks' => 'integer',
        'gift_benefit_active' => 'boolean',
        'delivery_free_threshold_kopecks' => 'integer',
        'delivery_outside_zone_surcharge_kopecks' => 'integer',
        'delivery_benefit_active' => 'boolean',
        'complement_set_benefit_active' => 'boolean',
        'complement_set_rolls_per_set' => 'integer',
    ];
}
