<?php

return [
    /**
     * Master switch: when false, OrderCreated is not exported to any accounting system
     * (per-system OAE_*_ENABLED flags are ignored).
     */
    'enabled' => filter_var(env('OAE_ENABLED', true), FILTER_VALIDATE_BOOLEAN),

    'systems' => [
        'stub' => [
            'enabled' => env('OAE_STUB_ENABLED', false),
        ],
        'frontpad' => [
            'enabled' => env('OAE_FRONTPAD_ENABLED', false),
            'secret' => env('OAE_FRONTPAD_SECRET') ?: env('FRONTPAD_API_SECRET', ''),
            'endpoint' => env('OAE_FRONTPAD_ENDPOINT') ?: (
                env('FRONTPAD_API_URL')
                    ? rtrim((string) env('FRONTPAD_API_URL'), '/').'?new_order'
                    : 'https://app.frontpad.ru/api/index.php?new_order'
            ),
            'point' => env('OAE_FRONTPAD_POINT'),
            'channel' => env('OAE_FRONTPAD_CHANNEL'),
            'affiliate' => env('OAE_FRONTPAD_AFFILIATE'),
            'person' => env('OAE_FRONTPAD_PERSON', 1),
            'hook_url' => env('OAE_FRONTPAD_HOOK_URL'),
            'hook_status' => array_values(array_filter(array_map(
                static fn (string $value): int => (int) trim($value),
                explode(',', (string) env('OAE_FRONTPAD_HOOK_STATUS', '1,10,11')),
            ))),
            'tags' => array_values(array_filter(array_map(
                static fn (string $value): string => trim($value),
                explode(',', (string) env('OAE_FRONTPAD_TAGS', '')),
            ))),
            'pay' => [
                'cash' => env('OAE_FRONTPAD_PAY_CASH', '1'),
                'card_courier' => env('OAE_FRONTPAD_PAY_CARD_COURIER', '2'),
                'card_online' => env('OAE_FRONTPAD_PAY_CARD_ONLINE', '2'),
            ],
            'product_bindings' => [],
        ],
    ],
];
