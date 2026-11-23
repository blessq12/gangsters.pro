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
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'ticket_id' => 'integer',
        'product_id' => 'integer',
        'quantity' => 'integer',
        'position' => 'integer',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(KitchenTicketModel::class, 'ticket_id');
    }
}
