<?php

namespace App\Infrastructure\Crm\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CRM_OrderHistory extends Model
{
    protected $table = 'CRM_order_history';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'client_id',
        'order_snapshot',
        'placed_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'order_snapshot' => 'array',
        'placed_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(CRM_Client::class, 'client_id');
    }
}
