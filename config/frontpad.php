<?php

return [
    'enabled' => filter_var(env('FRONTPAD_ENABLED', false), FILTER_VALIDATE_BOOLEAN),

    'secret' => env('FRONTPAD_SECRET', ''),

    'endpoint' => env('FRONTPAD_ENDPOINT', 'https://app.frontpad.ru/api/index.php?new_order'),

    'point' => env('FRONTPAD_POINT'),

    'channel' => env('FRONTPAD_CHANNEL'),

    'affiliate' => env('FRONTPAD_AFFILIATE'),

    'person' => env('FRONTPAD_PERSON', 1),

    'hook_url' => env('FRONTPAD_HOOK_URL'),

    'hook_status' => array_values(array_filter(array_map(
        static fn (string $value): int => (int) trim($value),
        explode(',', (string) env('FRONTPAD_HOOK_STATUS', '1,10,11')),
    ))),

    'tags' => array_values(array_filter(array_map(
        static fn (string $value): string => trim($value),
        explode(',', (string) env('FRONTPAD_TAGS', '')),
    ))),

    'pay' => [
        'cash' => env('FRONTPAD_PAY_CASH', '1'),
        'card_courier' => env('FRONTPAD_PAY_CARD_COURIER', '2'),
        'card_online' => env('FRONTPAD_PAY_CARD_ONLINE', '2'),
    ],
];
