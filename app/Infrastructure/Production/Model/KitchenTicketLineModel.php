<?php

namespace App\Infrastructure\Production\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class KitchenTicketLineModel extends Model
{
    public $timestamps = false;

    protected $table = 'PRD_ticket_lines';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ticket_id',
        'product_id',
        'name',
        'quantity',
        'position',
        'status',
        'done_by',
        'done_at',
        'packed_by',
        'packed_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'ticket_id' => 'integer',
        'product_id' => 'integer',
        'quantity' => 'integer',
        'position' => 'integer',
        'done_by' => 'integer',
        'packed_by' => 'integer',
        'done_at' => 'datetime',
        'packed_at' => 'datetime',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(KitchenTicketModel::class, 'ticket_id');
    }
}
