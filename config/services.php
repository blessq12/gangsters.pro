<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'yandex' => [
        'token' => env('YANDEX_TOKEN'),
        'counters' => array_values(
            array_filter(
                array_map('trim', explode(',', (string) env('YANDEX_COUNTERS', ''))),
                static fn (string $counter): bool => $counter !== '',
            ),
        ),
        'metrics_enabled' => filter_var(env('YANDEX_METRICS_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
        'metrics_service_class' => env('YANDEX_METRICS_SERVICE_CLASS', ''),
    ],
    'telegram' => [
        'token' => env('TELEGRAM_TOKEN'),
        'chat_id' => env('TELEGRAM_CHAT_ID'),
        'parse_mode' => env('TELEGRAM_PARSE_MODE', 'HTML'),
        'topics' => [
            'analytics' => env('TELEGRAM_TOPIC_ANALYTICS', 3),
            'error' => env('TELEGRAM_TOPIC_ERROR', 2),
            'event' => env('TELEGRAM_TOPIC_EVENT', 58),
        ],
    ],
    'frontpad' => [
        'enabled' => filter_var(env('FRONTPAD_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
        'api_url' => env('FRONTPAD_API_URL'),
        'api_secret' => env('FRONTPAD_API_SECRET'),
        'hook_url' => env('FRONTPAD_HOOK_URL', ''),
    ],
    'yandex_food' => [
        'enabled' => filter_var(env('YANDEX_FOOD_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
        'auth_token' => env('YANDEX_EDA_AUTH_TOKEN'),
        'client_id' => env('YANDEX_CLIENT_ID'),
        'client_secret' => env('YANDEX_CLIENT_SECRET'),
    ],
    'internal' => [
        'api_token' => env('INTERNAL_API_TOKEN'),
    ],

];
