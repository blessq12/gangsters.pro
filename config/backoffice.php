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
        ['method' => 'POST', 'path' => 'orders/*/reassign-branch', 'roles' => ['administrator']],
        ['method' => 'POST', 'path' => 'orders/*/status', 'roles' => ['owner', 'administrator']],
        ['method' => 'POST', 'path' => 'orders/*/complete-pickup', 'roles' => ['administrator']],
        ['method' => 'POST', 'path' => 'orders/*/notes', 'roles' => ['owner', 'administrator']],
        ['method' => 'POST', 'path' => 'orders', 'roles' => ['administrator']],
        ['method' => 'GET', 'path' => 'orders/*', 'roles' => ['owner', 'administrator']],
        ['method' => 'GET', 'path' => 'orders', 'roles' => ['owner', 'administrator']],

        // production
        ['method' => 'POST', 'path' => 'production/tickets/*/*', 'roles' => ['kitchen']],
        ['method' => 'GET', 'path' => 'production/queue', 'roles' => ['owner', 'administrator', 'kitchen']],
        ['method' => 'GET', 'path' => 'production/tickets/*', 'roles' => ['owner', 'administrator', 'kitchen']],

        // payments
        ['method' => 'POST', 'path' => 'payments/*/*', 'roles' => ['owner', 'administrator']],
        ['method' => 'GET', 'path' => 'payments/summary', 'roles' => ['owner', 'administrator']],
        ['method' => 'GET', 'path' => 'payments/*', 'roles' => ['owner', 'administrator']],
        ['method' => 'GET', 'path' => 'payments', 'roles' => ['owner', 'administrator']],

        // catalog — только справочник товаров
        ['method' => 'POST', 'path' => 'catalog/items/*/images', 'roles' => ['owner']],
        ['method' => 'PATCH', 'path' => 'catalog/items/*/images/*', 'roles' => ['owner']],
        ['method' => 'DELETE', 'path' => 'catalog/items/*/images/*', 'roles' => ['owner']],
        ['method' => 'POST', 'path' => 'catalog/*', 'roles' => ['owner']],
        ['method' => 'PUT', 'path' => 'catalog/*/*', 'roles' => ['owner']],
        ['method' => 'DELETE', 'path' => 'catalog/*/*', 'roles' => ['owner']],
        ['method' => 'GET', 'path' => 'catalog/products/*', 'roles' => ['owner', 'administrator']],
        ['method' => 'GET', 'path' => 'catalog/products', 'roles' => ['owner', 'administrator']],
        ['method' => 'GET', 'path' => 'catalog', 'roles' => ['owner', 'administrator']],

        // company + branch menu / structure / clone
        ['method' => 'POST', 'path' => 'company/branches/clone-settings', 'roles' => ['owner']],
        ['method' => 'GET', 'path' => 'company/branches/*/menu', 'roles' => ['owner']],
        ['method' => 'PUT', 'path' => 'company/branches/*/menu/*', 'roles' => ['owner']],
        ['method' => 'DELETE', 'path' => 'company/branches/*/menu/*', 'roles' => ['owner']],

        ['method' => 'PUT', 'path' => 'company/branches/*/categories/order', 'roles' => ['owner']],
        ['method' => 'PUT', 'path' => 'company/branches/*/categories/*/items', 'roles' => ['owner']],
        ['method' => 'GET', 'path' => 'company/branches/*/categories/*', 'roles' => ['owner', 'administrator']],
        ['method' => 'GET', 'path' => 'company/branches/*/categories', 'roles' => ['owner', 'administrator']],
        ['method' => 'POST', 'path' => 'company/branches/*/categories', 'roles' => ['owner']],
        ['method' => 'PUT', 'path' => 'company/branches/*/categories/*', 'roles' => ['owner']],
        ['method' => 'DELETE', 'path' => 'company/branches/*/categories/*', 'roles' => ['owner']],

        ['method' => 'GET', 'path' => 'company/branches/*/tags', 'roles' => ['owner', 'administrator']],
        ['method' => 'POST', 'path' => 'company/branches/*/tags', 'roles' => ['owner']],
        ['method' => 'PUT', 'path' => 'company/branches/*/tags/*', 'roles' => ['owner']],
        ['method' => 'DELETE', 'path' => 'company/branches/*/tags/*', 'roles' => ['owner']],

        ['method' => 'GET', 'path' => 'company/branches/*/sets/*', 'roles' => ['owner', 'administrator']],
        ['method' => 'GET', 'path' => 'company/branches/*/sets', 'roles' => ['owner', 'administrator']],
        ['method' => 'POST', 'path' => 'company/branches/*/sets', 'roles' => ['owner']],
        ['method' => 'PUT', 'path' => 'company/branches/*/sets/*', 'roles' => ['owner']],
        ['method' => 'DELETE', 'path' => 'company/branches/*/sets/*', 'roles' => ['owner']],

        ['method' => 'POST', 'path' => 'company/*', 'roles' => ['owner']],
        ['method' => 'PUT', 'path' => 'company/*/*', 'roles' => ['owner']],
        ['method' => 'POST', 'path' => 'company', 'roles' => ['owner']],
        ['method' => 'PUT', 'path' => 'company', 'roles' => ['owner']],
        ['method' => 'GET', 'path' => 'company/branches/*', 'roles' => ['owner', 'administrator', 'kitchen']],
        ['method' => 'GET', 'path' => 'company/branches', 'roles' => ['owner', 'administrator', 'kitchen']],
        ['method' => 'GET', 'path' => 'company/documents/*', 'roles' => ['owner', 'administrator', 'kitchen']],
        ['method' => 'GET', 'path' => 'company/documents', 'roles' => ['owner', 'administrator', 'kitchen']],
        ['method' => 'GET', 'path' => 'company', 'roles' => ['owner', 'administrator', 'kitchen']],

        // delivery config / zones
        ['method' => 'POST', 'path' => 'delivery/zones', 'roles' => ['owner']],
        ['method' => 'PUT', 'path' => 'delivery/zones/*', 'roles' => ['owner']],
        ['method' => 'PUT', 'path' => 'delivery/zones', 'roles' => ['owner']],
        ['method' => 'POST', 'path' => 'delivery', 'roles' => ['owner']],
        ['method' => 'PUT', 'path' => 'delivery', 'roles' => ['owner']],
        ['method' => 'GET', 'path' => 'delivery/zones', 'roles' => ['owner', 'administrator']],
        ['method' => 'GET', 'path' => 'delivery', 'roles' => ['owner', 'administrator']],

        // delivery route-sheets
        ['method' => null, 'path' => 'delivery/route-sheets/*/stops/*/settle', 'roles' => ['administrator', 'courier']],
        ['method' => null, 'path' => 'delivery/route-sheets/*/take', 'roles' => ['administrator', 'courier']],
        ['method' => 'GET', 'path' => 'delivery/route-sheets/*', 'roles' => ['owner', 'administrator']],
        ['method' => 'GET', 'path' => 'delivery/route-sheets', 'roles' => ['owner', 'administrator']],
        ['method' => 'POST', 'path' => 'delivery/route-sheets', 'roles' => ['administrator']],

        // courier
        ['method' => 'GET', 'path' => 'courier/route-sheets', 'roles' => ['courier']],

        // order/policy — правила оформления заказа
        ['method' => 'DELETE', 'path' => 'order/policy/roles', 'roles' => ['owner']],
        ['method' => 'POST', 'path' => 'order/policy/*', 'roles' => ['owner']],
        ['method' => 'POST', 'path' => 'order/policy', 'roles' => ['owner']],
        ['method' => 'PUT', 'path' => 'order/policy', 'roles' => ['owner']],
        ['method' => 'GET', 'path' => 'order/policy/roles', 'roles' => ['owner', 'administrator']],
        ['method' => 'GET', 'path' => 'order/policy/zones', 'roles' => ['owner', 'administrator']],
        ['method' => 'GET', 'path' => 'order/policy', 'roles' => ['owner', 'administrator']],

        // marketing
        ['method' => 'POST', 'path' => 'marketing/*', 'roles' => ['owner']],
        ['method' => 'POST', 'path' => 'marketing/*/*', 'roles' => ['owner']],
        ['method' => 'PUT', 'path' => 'marketing/*', 'roles' => ['owner']],
        ['method' => 'PUT', 'path' => 'marketing/*/*', 'roles' => ['owner']],
        ['method' => 'GET', 'path' => 'marketing/banners/*', 'roles' => ['owner', 'administrator']],
        ['method' => 'GET', 'path' => 'marketing/promotions/*', 'roles' => ['owner', 'administrator']],
        ['method' => 'GET', 'path' => 'marketing/banners', 'roles' => ['owner', 'administrator']],
        ['method' => 'GET', 'path' => 'marketing/promotions', 'roles' => ['owner', 'administrator']],
        ['method' => 'GET', 'path' => 'marketing', 'roles' => ['owner', 'administrator']],

        // employees (= staff)
        ['method' => 'POST', 'path' => 'employees/*/toggle-active', 'roles' => ['owner']],
        ['method' => 'POST', 'path' => 'employees', 'roles' => ['owner']],
        ['method' => 'PUT', 'path' => 'employees/*', 'roles' => ['owner']],
        ['method' => 'GET', 'path' => 'employees/*', 'roles' => ['owner']],
        ['method' => 'GET', 'path' => 'employees', 'roles' => ['owner', 'administrator']],

        // clients — саппорт: профиль и адреса
        ['method' => 'POST', 'path' => 'clients/*/addresses/*/default', 'roles' => ['owner', 'administrator']],
        ['method' => 'PUT', 'path' => 'clients/*/addresses/*', 'roles' => ['owner', 'administrator']],
        ['method' => 'DELETE', 'path' => 'clients/*/addresses/*', 'roles' => ['owner', 'administrator']],
        ['method' => 'POST', 'path' => 'clients/*/addresses', 'roles' => ['owner', 'administrator']],
        ['method' => 'PATCH', 'path' => 'clients/*', 'roles' => ['owner', 'administrator']],
        ['method' => 'GET', 'path' => 'clients/*', 'roles' => ['owner', 'administrator']],
        ['method' => 'GET', 'path' => 'clients', 'roles' => ['owner', 'administrator']],

        // warehouse
        ['method' => 'POST', 'path' => 'warehouse/branches/*/stock/adjust', 'roles' => ['owner']],
        ['method' => 'POST', 'path' => 'warehouse/branches/*/write-offs', 'roles' => ['owner']],
        ['method' => 'GET', 'path' => 'warehouse/branches/*/ingredients/*', 'roles' => ['owner']],
        ['method' => 'GET', 'path' => 'warehouse/branches/*/stock', 'roles' => ['owner']],
        ['method' => 'GET', 'path' => 'warehouse/branches/*/write-offs', 'roles' => ['owner']],
        ['method' => 'POST', 'path' => 'warehouse/*', 'roles' => ['owner']],
        ['method' => 'PUT', 'path' => 'warehouse/*/*', 'roles' => ['owner']],
        ['method' => 'GET', 'path' => 'warehouse/ingredients/*', 'roles' => ['owner']],
        ['method' => 'GET', 'path' => 'warehouse/*', 'roles' => ['owner']],
    ],
];
