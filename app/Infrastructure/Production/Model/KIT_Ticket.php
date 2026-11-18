<?php

namespace App\Infrastructure\Production\Model;

use Illuminate\Database\Eloquent\Model;

final class KIT_Ticket extends Model
{
    protected $table = 'KIT_tickets';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'kitchen_id',
        'status',
        'started_at',
        'completed_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'order_id' => 'integer',
        'kitchen_id' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
}
