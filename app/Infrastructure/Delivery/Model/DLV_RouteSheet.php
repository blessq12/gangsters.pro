<?php

namespace App\Infrastructure\Delivery\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class DLV_RouteSheet extends Model
{
    protected $table = 'DLV_route_sheets';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'status',
        'courier_id',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'courier_id' => 'integer',
    ];

    public function stops(): HasMany
    {
        return $this->hasMany(DLV_RouteStop::class, 'route_sheet_id')
            ->orderBy('position')
            ->orderBy('id');
    }
}
