<?php

namespace App\Infrastructure\Marketing\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $body
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
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
