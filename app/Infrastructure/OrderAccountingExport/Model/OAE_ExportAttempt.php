<?php

namespace App\Infrastructure\OrderAccountingExport\Model;

use Illuminate\Database\Eloquent\Model;

final class OAE_ExportAttempt extends Model
{
    public $timestamps = false;

    protected $table = 'OAE_export_attempts';

    protected $fillable = [
        'order_id',
        'system_code',
        'status',
        'attempt',
        'external_reference',
        'error_message',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
