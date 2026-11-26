<?php

namespace App\Infrastructure\Delivery\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $branch_id
 * @property string $status
 * @property int $courier_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class RouteSheetModel extends Model
{
    protected $table = 'DLV_route_sheets';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'branch_id',
        'status',
        'courier_id',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'branch_id' => 'integer',
        'courier_id' => 'integer',
    ];

    public function stops(): HasMany
    {
        return $this->hasMany(RouteStopModel::class, 'route_sheet_id')
            ->orderBy('position')
            ->orderBy('id');
    }
}
