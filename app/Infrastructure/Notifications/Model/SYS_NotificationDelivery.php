<?php

namespace App\Infrastructure\Notifications\Model;

use Illuminate\Database\Eloquent\Model;

final class SYS_NotificationDelivery extends Model
{
    public $timestamps = false;

    protected $table = 'notification_deliveries';

    protected $fillable = [
        'channel',
        'event_type',
        'recipient',
        'status',
        'error_message',
        'payload_json',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
