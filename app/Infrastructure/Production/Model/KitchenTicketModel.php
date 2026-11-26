<?php

namespace App\Infrastructure\Production\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $order_id
 * @property int $branch_id
 * @property int $assignee_employee_id
 * @property string $status
 * @property Carbon|null $started_at
 * @property Carbon|null $completed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class KitchenTicketModel extends Model
{
    protected $table = 'PRD_tickets';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'branch_id',
        'assignee_employee_id',
        'status',
        'started_at',
        'completed_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'order_id' => 'integer',
        'branch_id' => 'integer',
        'assignee_employee_id' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(KitchenTicketLineModel::class, 'ticket_id')
            ->orderBy('position')
            ->orderBy('id');
    }
}
