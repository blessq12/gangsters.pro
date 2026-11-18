<?php

/**
 * access: первый подходящий rule выигрывает (специфичные — выше).
 * path — относительно /api/backoffice/; * = один сегмент; method null = любой.
 */
return [
    'enabled' => filter_var(env('BACKOFFICE_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
    'auth_required' => filter_var(env('BACKOFFICE_AUTH_REQUIRED', false), FILTER_VALIDATE_BOOLEAN),

    'access' => [
        // orders — операции
        ['method' => 'POST', 'path' => 'orders/*/accept', 'roles' => ['administrator']],
        ['method' => 'POST', 'path' => 'orders/*/status', 'roles' => ['administrator']],
        ['method' => 'POST', 'path' => 'orders/*/complete-pickup', 'roles' => ['administrator']],
        ['method' => 'POST', 'path' => 'orders', 'roles' => ['administrator']],
        ['method' => 'GET', 'path' => 'orders/*', 'roles' => ['owner', 'administrator']],
        ['method' => 'GET', 'path' => 'orders', 'roles' => ['owner', 'administrator']],

        // production
        ['method' => 'POST', 'path' => 'production/tickets/*/*', 'roles' => ['kitchen']],
        ['method' => 'GET', 'path' => 'production/queue', 'roles' => ['owner', 'administrator', 'kitchen']],
        ['method' => 'GET', 'path' => 'production/tickets/*', 'roles' => ['owner', 'administrator', 'kitchen']],

        // payments
        ['method' => 'POST', 'path' => 'payments/*/*', 'roles' => ['administrator']],
        ['method' => 'GET', 'path' => 'payments/*', 'roles' => ['owner', 'administrator']],

        // catalog
        ['method' => 'POST', 'path' => 'catalog/*', 'roles' => ['owner']],
        ['method' => 'PUT', 'path' => 'catalog/*/*', 'roles' => ['owner']],
        ['method' => 'GET', 'path' => 'catalog', 'roles' => ['owner', 'administrator']],

        // company
        ['method' => 'POST', 'path' => 'company/*', 'roles' => ['owner']],
        ['method' => 'PUT', 'path' => 'company/*/*', 'roles' => ['owner']],
        ['method' => 'POST', 'path' => 'company', 'roles' => ['owner']],
        ['method' => 'PUT', 'path' => 'company', 'roles' => ['owner']],
        ['method' => 'GET', 'path' => 'company', 'roles' => ['owner', 'administrator']],

        // delivery config / zones
        ['method' => 'POST', 'path' => 'delivery/zones', 'roles' => ['owner']],
        ['method' => 'PUT', 'path' => 'delivery/zones/*', 'roles' => ['owner']],
        ['method' => 'POST', 'path' => 'delivery', 'roles' => ['owner']],
        ['method' => 'PUT', 'path' => 'delivery', 'roles' => ['owner']],
        ['method' => 'GET', 'path' => 'delivery', 'roles' => ['owner', 'administrator']],

        // delivery route-sheets
        ['method' => null, 'path' => 'delivery/route-sheets/*/stops/*/settle', 'roles' => ['administrator']],
        ['method' => null, 'path' => 'delivery/route-sheets/*/take', 'roles' => ['administrator']],
        ['method' => null, 'path' => 'delivery/route-sheets/*', 'roles' => ['administrator']],
        ['method' => null, 'path' => 'delivery/route-sheets', 'roles' => ['administrator']],

        // courier
        ['method' => 'GET', 'path' => 'courier/route-sheets', 'roles' => ['courier']],

        // promotion-policy
        ['method' => 'POST', 'path' => 'promotion-policy/*', 'roles' => ['owner']],
        ['method' => 'POST', 'path' => 'promotion-policy', 'roles' => ['owner']],
        ['method' => 'PUT', 'path' => 'promotion-policy', 'roles' => ['owner']],
        ['method' => 'GET', 'path' => 'promotion-policy', 'roles' => ['owner', 'administrator']],

        // marketing
        ['method' => 'POST', 'path' => 'marketing/*', 'roles' => ['owner']],
        ['method' => 'PUT', 'path' => 'marketing/*', 'roles' => ['owner']],
        ['method' => 'PUT', 'path' => 'marketing/*/*', 'roles' => ['owner']],
        ['method' => 'GET', 'path' => 'marketing', 'roles' => ['owner', 'administrator']],

        // employees (= staff)
        ['method' => 'POST', 'path' => 'employees/*/toggle-active', 'roles' => ['owner']],
        ['method' => 'POST', 'path' => 'employees', 'roles' => ['owner']],
        ['method' => 'PUT', 'path' => 'employees/*', 'roles' => ['owner']],
        ['method' => 'DELETE', 'path' => 'employees/*', 'roles' => ['owner']],
        ['method' => 'GET', 'path' => 'employees', 'roles' => ['owner']],

        // clients
        ['method' => 'GET', 'path' => 'clients/*', 'roles' => ['owner', 'administrator']],
        ['method' => 'GET', 'path' => 'clients', 'roles' => ['owner', 'administrator']],

        // warehouse
        ['method' => 'POST', 'path' => 'warehouse/*', 'roles' => ['owner']],
        ['method' => 'POST', 'path' => 'warehouse/*/*', 'roles' => ['owner']],
        ['method' => 'PUT', 'path' => 'warehouse/*/*', 'roles' => ['owner']],
        ['method' => 'GET', 'path' => 'warehouse/*', 'roles' => ['owner']],
    ],
];
