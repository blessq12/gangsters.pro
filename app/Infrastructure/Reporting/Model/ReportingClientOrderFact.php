<?php

namespace App\Infrastructure\Reporting\Model;

use Illuminate\Database\Eloquent\Model;

final class ReportingClientOrderFact extends Model
{
    protected $table = 'reporting_client_order_facts';

    public $incrementing = false;

    protected $primaryKey = 'order_id';

    protected $keyType = 'string';

    protected $fillable = [
        'order_id',
        'client_id',
        'payment_status',
        'total',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'client_id' => 'int',
        'total' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
