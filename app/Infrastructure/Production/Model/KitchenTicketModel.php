<?php

namespace App\Infrastructure\Production\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
