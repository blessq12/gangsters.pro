<?php

return [

    'delivery_log' => [
        // null — хранить без ограничения; число — удалять записи старше N дней при записи новой.
        'retention_days' => env('NOTIFICATION_DELIVERY_LOG_RETENTION_DAYS') !== null
            ? (int) env('NOTIFICATION_DELIVERY_LOG_RETENTION_DAYS')
            : null,

        'payload_max_length' => (int) env('NOTIFICATION_DELIVERY_LOG_PAYLOAD_MAX_LENGTH', 2000),
    ],

];
