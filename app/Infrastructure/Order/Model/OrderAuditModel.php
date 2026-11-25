<?php

namespace App\Infrastructure\Order\Model;

use Illuminate\Database\Eloquent\Model;

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
