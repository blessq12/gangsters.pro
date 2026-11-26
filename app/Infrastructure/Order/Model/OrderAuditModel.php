<?php

namespace App\Infrastructure\Order\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $order_id
 * @property int $author_employee_id
 * @property array $changes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class OrderAuditModel extends Model
{
    public $timestamps = false;

    protected $table = 'ORD_order_audits';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'author_employee_id',
        'changes',
        'created_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'order_id' => 'integer',
        'author_employee_id' => 'integer',
        'changes' => 'array',
        'created_at' => 'datetime',
    ];
}
