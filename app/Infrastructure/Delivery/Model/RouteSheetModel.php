<?php

namespace App\Infrastructure\Delivery\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
