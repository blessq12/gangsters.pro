<?php

use App\Domain\Crm\Event\ClientCreated;
use App\Domain\Crm\Event\ClientPasswordChanged;
use App\Domain\Order\Event\OrderCreated;
use App\Integration\Frontpad\Listener\OnOrderCreated;

/**
 * Матрица подписок: доменное событие → слушатели Integration / Infrastructure.
 *
 * Слушатель: class-string с методом handle, или [class-string, 'method'].
 *
 * @return array{
 *     listen: array<class-string, list<class-string|array{0: class-string, 1: string}>>
 * }
 */
return [
    'listen' => [
        OrderCreated::class => [
            OnOrderCreated::class,
        ],

        ClientCreated::class => [
            // слушатели подписок CRM
        ],

        ClientPasswordChanged::class => [
            // слушатели подписок CRM
        ],
    ],
];
