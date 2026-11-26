<?php

namespace App\Infrastructure\Delivery\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $min_order_amount_kopecks
 * @property int $average_delivery_time_minutes
 * @property int $branch_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class DeliveryConfigurationModel extends Model
{
    protected $table = 'DLV_configurations';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'min_order_amount_kopecks',
        'average_delivery_time_minutes',
        'branch_id',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'min_order_amount_kopecks' => 'integer',
        'average_delivery_time_minutes' => 'integer',
        'branch_id' => 'integer',
    ];

    public function zones(): HasMany
    {
        return $this->hasMany(DeliveryZoneModel::class, 'configuration_id');
    }
}
