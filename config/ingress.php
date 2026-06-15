<?php

return [
    'partners' => [
        'stub' => [
            'enabled' => env('INGRESS_STUB_ENABLED', true),
            'api_key' => env('INGRESS_STUB_API_KEY', 'stub-dev-key'),
        ],
        'yandex-eda' => [
            'enabled' => env('INGRESS_YANDEX_EDA_ENABLED', false),
            'api_key' => env('INGRESS_YANDEX_EDA_API_KEY', ''),
        ],
        'chibbis' => [
            'enabled' => env('INGRESS_CHIBBIS_ENABLED', false),
            'api_key' => env('INGRESS_CHIBBIS_API_KEY', ''),
        ],
        'kuper' => [
            'enabled' => env('INGRESS_KUPER_ENABLED', false),
            'api_key' => env('INGRESS_KUPER_API_KEY', ''),
        ],
    ],
];
