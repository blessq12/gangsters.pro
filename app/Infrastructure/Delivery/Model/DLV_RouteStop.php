<?php

namespace App\Infrastructure\Delivery\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class DLV_RouteStop extends Model
{
    public $timestamps = false;

    protected $table = 'DLV_route_stops';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'route_sheet_id',
        'order_id',
        'position',
        'outcome',
        'reason',
    ];

    /**
     * @return BelongsTo<DLV_RouteSheet, $this>
     */
    public function sheet(): BelongsTo
    {
        return $this->belongsTo(DLV_RouteSheet::class, 'route_sheet_id');
    }
}
