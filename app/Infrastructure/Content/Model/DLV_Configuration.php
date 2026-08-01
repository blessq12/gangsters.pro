<?php

namespace App\Infrastructure\Content\Model;

use Illuminate\Database\Eloquent\Model;

class DLV_Configuration extends Model
{
    protected $table = 'DLV_configuration';

    protected $fillable = [
        'min_order_amount_kopecks',
        'delivery_fee_kopecks',
        'outside_zone_delivery_fee_kopecks',
        'average_delivery_time_minutes',
        'kitchen_city',
        'kitchen_street',
        'kitchen_house',
        'kitchen_address_comment',
        'kitchen_address',
        'kitchen_latitude',
        'kitchen_longitude',
        'delivery_zone_geojson',
    ];

    protected $casts = [
        'min_order_amount_kopecks' => 'integer',
        'delivery_fee_kopecks' => 'integer',
        'outside_zone_delivery_fee_kopecks' => 'integer',
        'average_delivery_time_minutes' => 'integer',
        'kitchen_latitude' => 'float',
        'kitchen_longitude' => 'float',
        'delivery_zone_geojson' => 'array',
    ];
}
