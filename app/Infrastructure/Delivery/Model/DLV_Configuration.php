<?php

namespace App\Infrastructure\Delivery\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class DLV_Configuration extends Model
{
    protected $table = 'DLV_configurations';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'min_order_amount_kopecks',
        'average_delivery_time_minutes',
        'kitchen_address',
        'kitchen_latitude',
        'kitchen_longitude',
        'company_kitchen_id',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'min_order_amount_kopecks' => 'integer',
        'average_delivery_time_minutes' => 'integer',
        'kitchen_address' => 'array',
        'kitchen_latitude' => 'float',
        'kitchen_longitude' => 'float',
        'company_kitchen_id' => 'integer',
    ];

    public function zones(): HasMany
    {
        return $this->hasMany(DLV_Zone::class, 'configuration_id');
    }
}
