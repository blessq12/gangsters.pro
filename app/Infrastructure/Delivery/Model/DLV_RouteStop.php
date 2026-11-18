<?php

namespace App\Infrastructure\Delivery\Model;

use Illuminate\Database\Eloquent\Model;

final class DLV_RouteStop extends Model
{
    protected $table = 'DLV_route_stops';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'route_sheet_id',
        'order_id',
        'position',
        'status',
        'settlement_note',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'route_sheet_id' => 'integer',
        'order_id' => 'integer',
        'position' => 'integer',
    ];
}
