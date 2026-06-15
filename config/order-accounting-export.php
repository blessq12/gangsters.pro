<?php

return [
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
        'iiko' => [
            'enabled' => env('OAE_IIKO_ENABLED', false),
            'api_login' => env('OAE_IIKO_API_LOGIN', ''),
            'base_url' => env('OAE_IIKO_BASE_URL', 'https://api-ru.iiko.services'),
            'organization_id' => env('OAE_IIKO_ORGANIZATION_ID', ''),
            'terminal_group_id' => env('OAE_IIKO_TERMINAL_GROUP_ID', ''),
            'default_street_id' => env('OAE_IIKO_DEFAULT_STREET_ID'),
            'default_latitude' => env('OAE_IIKO_DEFAULT_LATITUDE'),
            'default_longitude' => env('OAE_IIKO_DEFAULT_LONGITUDE'),
            'payment_types' => [
                'cash' => [
                    'kind' => env('OAE_IIKO_PAYMENT_CASH_KIND', 'Cash'),
                    'id' => env('OAE_IIKO_PAYMENT_CASH_ID', ''),
                ],
                'card_courier' => [
                    'kind' => env('OAE_IIKO_PAYMENT_CARD_COURIER_KIND', 'Card'),
                    'id' => env('OAE_IIKO_PAYMENT_CARD_COURIER_ID', ''),
                ],
                'card_online' => [
                    'kind' => env('OAE_IIKO_PAYMENT_CARD_ONLINE_KIND', 'Card'),
                    'id' => env('OAE_IIKO_PAYMENT_CARD_ONLINE_ID', ''),
                ],
            ],
            'product_bindings' => [],
        ],
    ],
];
