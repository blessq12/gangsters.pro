<?php

return [
    'enabled' => filter_var(env('YANDEX_FOOD_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
    'auth_token' => env('YANDEX_EDA_AUTH_TOKEN', ''),
    'client_id' => env('YANDEX_CLIENT_ID', ''),
    'client_secret' => env('YANDEX_CLIENT_SECRET', ''),
    'restaurant_id' => env('YANDEX_FOOD_RESTAURANT_ID', '1'),
    'partner_code' => 'yandex-eda',
];
