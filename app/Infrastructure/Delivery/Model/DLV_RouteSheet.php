<?php

namespace App\Infrastructure\Delivery\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class DLV_RouteSheet extends Model
{
    public $timestamps = false;

    protected $table = 'DLV_route_sheets';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'status',
        'created_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * @return HasMany<DLV_RouteStop, $this>
     */
    public function stops(): HasMany
    {
        return $this->hasMany(DLV_RouteStop::class, 'route_sheet_id')->orderBy('position');
    }
}
