<?php

namespace App\Infrastructure\Order\Model;

use Illuminate\Database\Eloquent\Model;

final class OrderNoteModel extends Model
{
    public $timestamps = false;

    protected $table = 'ORD_order_notes';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'body',
        'author_employee_id',
        'created_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'order_id' => 'integer',
        'author_employee_id' => 'integer',
        'created_at' => 'datetime',
    ];
}
