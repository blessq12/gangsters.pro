<?php

namespace App\Infrastructure\Marketing\Model;

use Illuminate\Database\Eloquent\Model;

final class MarketingDeliveryConditionsModel extends Model
{
    protected $table = 'MKT_delivery_conditions';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'body',
        'is_active',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];
}
