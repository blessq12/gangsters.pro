<?php

namespace App\Infrastructure\Delivery\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $route_sheet_id
 * @property int $order_id
 * @property int $position
 * @property string $status
 * @property string $settlement_note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class RouteStopModel extends Model
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

    public function sheet(): BelongsTo
    {
        return $this->belongsTo(RouteSheetModel::class, 'route_sheet_id');
    }
}
