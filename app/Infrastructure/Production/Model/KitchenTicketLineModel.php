<?php

namespace App\Infrastructure\Production\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $ticket_id
 * @property int $product_id
 * @property string $name
 * @property int $quantity
 * @property int $position
 * @property string $status
 * @property int $done_by
 * @property Carbon|null $done_at
 * @property int $packed_by
 * @property Carbon|null $packed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
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
