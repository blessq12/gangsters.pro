<?php

namespace App\Infrastructure\AggregatorIngress\Model;

use Illuminate\Database\Eloquent\Model;

final class ING_IngressAudit extends Model
{
    public $timestamps = false;

    protected $table = 'ING_ingress_audits';

    protected $fillable = [
        'partner_code',
        'external_order_id',
        'order_id',
        'result',
        'raw_payload',
        'created_at',
    ];

    protected $casts = [
        'raw_payload' => 'array',
        'created_at' => 'datetime',
    ];
}
