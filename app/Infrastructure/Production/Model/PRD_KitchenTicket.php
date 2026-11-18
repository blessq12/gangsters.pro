<?php

namespace App\Infrastructure\Production\Model;

use Illuminate\Database\Eloquent\Model;

final class PRD_KitchenTicket extends Model
{
    protected $table = 'PRD_kitchen_tickets';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'status',
        'started_at',
        'completed_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'order_id' => 'integer',
        'status' => 'string',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
}
