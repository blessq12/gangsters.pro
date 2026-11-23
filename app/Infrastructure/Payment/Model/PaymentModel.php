<?php

namespace App\Infrastructure\Payment\Model;

use Illuminate\Database\Eloquent\Model;

final class PaymentModel extends Model
{
    protected $table = 'PAY_payments';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'method',
        'change_from_rubles',
        'amount_kopecks',
        'status',
        'paid_at',
        'refunded_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'order_id' => 'integer',
        'change_from_rubles' => 'integer',
        'amount_kopecks' => 'integer',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];
}
