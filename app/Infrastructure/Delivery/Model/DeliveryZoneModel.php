<?php

namespace App\Infrastructure\Delivery\Model;

use Illuminate\Database\Eloquent\Model;

final class DeliveryZoneModel extends Model
{
    protected $table = 'DLV_zones';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'configuration_id',
        'zone_key',
        'name',
        'fee_kopecks',
        'is_remote',
        'geojson',
        'delivery_product_id',
        'sort_order',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'configuration_id' => 'integer',
        'fee_kopecks' => 'integer',
        'is_remote' => 'boolean',
        'geojson' => 'array',
        'delivery_product_id' => 'integer',
        'sort_order' => 'integer',
    ];
}
